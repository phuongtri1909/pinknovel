<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Config;
use App\Models\CardDeposit;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

class CardDepositController extends Controller
{
    public $coinExchangeRate;
    public $coinCardPercent;
    public $cardWrongAmountPenalty;
    public $tsrPartnerKey;
    public $tsrPartnerId;

    public function __construct()
    {
        $this->coinExchangeRate = Config::getConfig('coin_exchange_rate', 100);
        $this->coinCardPercent = Config::getConfig('coin_card_percent', 30);
        $this->cardWrongAmountPenalty = Config::getConfig('card_wrong_amount_penalty', 50);
        $this->tsrPartnerKey = env('TSR_PARTNER_KEY', '6dd372151552c79c1fbabc49d02829f4');
        $this->tsrPartnerId = env('TSR_PARTNER_ID', '0601968451');
        // Log::info('CardDepositController initialized', [
        //     'coin_exchange_rate' => $this->coinExchangeRate,
        //     'coin_card_percent' => $this->coinCardPercent,
        //     'tsr_partner_id' => $this->tsrPartnerId,
        //     'tsr_partner_key' => $this->tsrPartnerKey
        // ]);
    }

    /**
     * Hiển thị trang nạp xu bằng thẻ cào
     */
    public function index()
    {
        $user = Auth::user();

        // Lấy lịch sử nạp thẻ của user
        $cardDeposits = CardDeposit::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $coinCardPercent = $this->coinCardPercent;
        $coinExchangeRate = $this->coinExchangeRate;

        return view('pages.information.deposit.card_deposit', compact(
            'user',
            'cardDeposits',
            'coinCardPercent',
            'coinExchangeRate'
        ));
    }

    /**
     * Xử lý nạp thẻ cào
     */
    public function store(Request $request)
    {
        // Validate với CARD_TYPES dynamic
        $allowedCardTypes = array_keys(CardDeposit::CARD_TYPES);
        $allowedAmounts = array_keys(CardDeposit::CARD_VALUES);

        $request->validate([
            'telco' => 'required|in:' . implode(',', $allowedCardTypes),
            'serial' => 'required|string|min:10|max:20',
            'code' => 'required|string|min:10|max:20',
            'amount' => 'required|integer|in:' . implode(',', $allowedAmounts)
        ], [
            'telco.required' => 'Vui lòng chọn loại thẻ',
            'telco.in' => 'Loại thẻ không hợp lệ',
            'serial.required' => 'Vui lòng nhập số serial',
            'serial.min' => 'Số serial phải có ít nhất 10 ký tự',
            'serial.max' => 'Số serial không được quá 20 ký tự',
            'code.required' => 'Vui lòng nhập mã thẻ',
            'code.min' => 'Mã thẻ phải có ít nhất 10 ký tự',
            'code.max' => 'Mã thẻ không được quá 20 ký tự',
            'amount.required' => 'Vui lòng chọn mệnh giá',
            'amount.in' => 'Mệnh giá không hợp lệ'
        ]);

        // Kiểm tra thẻ đã được sử dụng thành công chưa (trong toàn bộ hệ thống)
        $existingSuccessCard = CardDeposit::where('serial', $request->serial)
            ->where('pin', $request->code)
            ->where('status', CardDeposit::STATUS_SUCCESS)
            ->first();

        if ($existingSuccessCard) {
            // Log::warning('Attempted to reuse successful card', [
            //     'user_id' => Auth::id(),
            //     'existing_user_id' => $existingSuccessCard->user_id,
            //     'existing_request_id' => $existingSuccessCard->request_id,
            //     'serial' => substr($request->serial, 0, 4) . '****',
            //     'code' => substr($request->code, 0, 4) . '****',
            //     'processed_at' => $existingSuccessCard->processed_at
            // ]);

            return response()->json([
                'success' => false,
                'message' => 'Thẻ này đã được nạp thành công trước đó vào lúc ' .
                    $existingSuccessCard->processed_at->format('d/m/Y H:i:s') .
                    '. Không thể sử dụng lại thẻ đã nạp.',
                'existing_status' => 'success',
                'processed_at' => $existingSuccessCard->processed_at->format('d/m/Y H:i:s')
            ], 400);
        }

        // Kiểm tra thẻ đang được xử lý
        $existingProcessingCard = CardDeposit::where('serial', $request->serial)
            ->where('pin', $request->code)
            ->whereIn('status', [CardDeposit::STATUS_PENDING, CardDeposit::STATUS_PROCESSING])
            ->where('created_at', '>=', now()->subMinutes(30)) // Trong vòng 30 phút
            ->first();

        if ($existingProcessingCard) {
            if ($existingProcessingCard->user_id == Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thẻ này đang được xử lý. Vui lòng chờ kết quả.',
                    'existing_status' => $existingProcessingCard->status,
                    'card_deposit_id' => $existingProcessingCard->id,
                    'created_at' => $existingProcessingCard->created_at->format('d/m/Y H:i:s')
                ], 400);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Thẻ này đang được xử lý bởi người dùng khác.',
                    'existing_status' => 'processing'
                ], 400);
            }
        }

        // Kiểm tra rate limiting cho user (tối đa 5 lần nạp/giờ)
        $recentAttempts = CardDeposit::where('user_id', Auth::id())
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentAttempts >= 30) {
            Log::warning('Rate limit exceeded for card deposit', [
                'user_id' => Auth::id(),
                'attempts_last_hour' => $recentAttempts
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Bạn đã nạp quá nhiều lần trong 1 giờ qua. Vui lòng thử lại sau.',
                'rate_limit' => true,
                'attempts' => $recentAttempts,
                'max_attempts' => 5
            ], 429);
        }

        try {
            DB::beginTransaction();

            // Tính toán số xu và phí
            $amount = $request->amount;
            $feePercent = $this->coinCardPercent;
            $feeAmount = ($amount * $feePercent) / 100;
            $amountAfterFee = $amount - $feeAmount;
            $coins = floor($amountAfterFee / $this->coinExchangeRate);

            // Tạo request ID unique theo format TSR
            $requestId = $this->generateUniqueRequestId();

            // Tạo record trong database
            $cardDeposit = CardDeposit::create([
                'user_id' => Auth::id(),
                'type' => $request->telco,
                'serial' => $request->serial,
                'pin' => $request->code,
                'amount' => $amount,
                'coins' => $coins,
                'fee_percent' => $feePercent,
                'fee_amount' => $feeAmount,
                'request_id' => $requestId,
                'status' => CardDeposit::STATUS_PENDING,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Log::info('New card deposit created', [
            //     'card_deposit_id' => $cardDeposit->id,
            //     'user_id' => Auth::id(),
            //     'request_id' => $requestId,
            //     'telco' => $request->telco,
            //     'amount' => $amount,
            //     'coins' => $coins
            // ]);

            // Gọi API TheSieuRe
            $apiResponse = $this->callTSRApi($cardDeposit);

            if ($apiResponse['success']) {
                $cardDeposit->markAsProcessing(
                    $apiResponse['transaction_id'] ?? null,
                    $apiResponse['response_data']
                );

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Thẻ đã được gửi xử lý. Kết quả sẽ được cập nhật trong vài phút.',
                    'card_deposit_id' => $cardDeposit->id,
                    'request_id' => $requestId,
                    'status' => $apiResponse['status'],
                    'response_message' => $apiResponse['message'],
                    'expected_coins' => $coins,
                    'estimated_time' => '1-5 phút'
                ]);
            } else {
                $cardDeposit->markAsFailed(
                    $apiResponse['message'],
                    $apiResponse['response_data']
                );

                DB::commit();

                return response()->json([
                    'success' => false,
                    'message' => $apiResponse['message'],
                    'card_deposit_id' => $cardDeposit->id,
                    'api_status' => $apiResponse['status'] ?? null
                ], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Card deposit error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'serial' => substr($request->serial, 0, 4) . '****',
                'code' => substr($request->code, 0, 4) . '****',
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xử lý thẻ. Vui lòng thử lại sau.',
                'error_code' => 'SYSTEM_ERROR'
            ], 500);
        }
    }

    /**
     * Tạo request ID unique
     */
    private function generateUniqueRequestId()
    {
        do {
            $requestId = rand(100000000, 999999999);
        } while (CardDeposit::where('request_id', $requestId)->exists());

        return $requestId;
    }

    /**
     * Gọi API TheSieuRe bằng Guzzle HTTP Client
     */
    private function callTSRApi(CardDeposit $cardDeposit)
    {
        try {
            $url = 'https://thesieure88.com/chargingws/v2';

            // Tính signature theo TSR: md5(partner_key + code + serial)
            $signature = md5($this->tsrPartnerKey . $cardDeposit->pin . $cardDeposit->serial);
            // Log::info('TSR API Signature', [
            //     'partner_key' => $this->tsrPartnerKey,
            //     'code' => $cardDeposit->pin,
            //     'serial' => $cardDeposit->serial,
            //     'signature' => $signature
            // ]);

            // Tạo multipart data như trong Postman
            $multipart = [
                [
                    'name' => 'telco',
                    'contents' => $cardDeposit->type
                ],
                [
                    'name' => 'code',
                    'contents' => $cardDeposit->pin
                ],
                [
                    'name' => 'serial',
                    'contents' => $cardDeposit->serial
                ],
                [
                    'name' => 'amount',
                    'contents' => (string)$cardDeposit->amount
                ],
                [
                    'name' => 'request_id',
                    'contents' => (string)$cardDeposit->request_id
                ],
                [
                    'name' => 'partner_id',
                    'contents' => $this->tsrPartnerId
                ],
                [
                    'name' => 'sign',
                    'contents' => $signature
                ],
                [
                    'name' => 'command',
                    'contents' => 'charging'
                ]
            ];

            // Log request data để debug
            // Log::info('TSR API Request (Guzzle)', [
            //     'url' => $url,
            //     'partner_id' => $this->tsrPartnerId,
            //     'request_id' => $cardDeposit->request_id,
            //     'telco' => $cardDeposit->type,
            //     'amount' => $cardDeposit->amount,
            //     'code' => substr($cardDeposit->pin, 0, 4) . '****',
            //     'serial' => substr($cardDeposit->serial, 0, 4) . '****',
            //     'sign' => $signature
            // ]);

            // Khởi tạo Guzzle client
            $client = new Client([
                'timeout' => 30,
                'connect_timeout' => 10,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept' => 'application/json, text/plain, */*',
                    'Accept-Language' => 'vi-VN,vi;q=0.9,en;q=0.8',
                    'Referer' => request()->url()
                ]
            ]);

            // Gửi request
            $response = $client->post($url, [
                'multipart' => $multipart,
                'timeout' => 30,
                'connect_timeout' => 10
            ]);

            // Lấy response body
            $responseBody = $response->getBody()->getContents();
            $httpCode = $response->getStatusCode();
            $responseData = json_decode($responseBody, true);

            // Log::info('TSR API Response (Guzzle)', [
            //     'request_id' => $cardDeposit->request_id,
            //     'http_code' => $httpCode,
            //     'response_body' => $responseBody,
            //     'response_data' => $responseData
            // ]);

            if ($responseData && isset($responseData['status'])) {
                switch ((int)$responseData['status']) {
                    case 99:
                        // Gửi thẻ thành công, đợi duyệt
                        return [
                            'success' => true,
                            'status' => 99,
                            'message' => 'Thẻ đang được xử lý, vui lòng chờ kết quả',
                            'transaction_id' => $responseData['trans_id'] ?? null,
                            'response_data' => $responseData
                        ];

                    case 1:
                        // Thành công
                        return [
                            'success' => true,
                            'status' => 1,
                            'message' => 'Nạp thẻ thành công',
                            'transaction_id' => $responseData['trans_id'] ?? null,
                            'response_data' => $responseData
                        ];

                    case 2:
                        // Thành công nhưng sai mệnh giá
                        return [
                            'success' => true,
                            'status' => 2,
                            'message' => 'Thẻ đúng nhưng sai mệnh giá',
                            'transaction_id' => $responseData['trans_id'] ?? null,
                            'response_data' => $responseData
                        ];

                    case 3:
                        // Thẻ lỗi
                        return [
                            'success' => false,
                            'status' => 3,
                            'message' => $responseData['message'] ?? 'Thẻ không hợp lệ hoặc đã được sử dụng',
                            'response_data' => $responseData
                        ];

                    case 4:
                        // Bảo trì
                        return [
                            'success' => false,
                            'status' => 4,
                            'message' => 'Hệ thống đang bảo trì, vui lòng thử lại sau',
                            'response_data' => $responseData
                        ];

                    default:
                        return [
                            'success' => false,
                            'message' => $responseData['message'] ?? 'Lỗi không xác định từ hệ thống xử lý thẻ',
                            'response_data' => $responseData
                        ];
                }
            }

            return [
                'success' => false,
                'message' => 'Không nhận được phản hồi hợp lệ từ hệ thống xử lý thẻ',
                'response_data' => $responseData
            ];
        } catch (ConnectException $e) {
            // Log::error('TSR API Connection Error: ' . $e->getMessage(), [
            //     'request_id' => $cardDeposit->request_id ?? 'unknown',
            //     'error_type' => 'connection',
            //     'trace' => $e->getTraceAsString()
            // ]);

            return [
                'success' => false,
                'message' => 'Không thể kết nối đến hệ thống xử lý thẻ. Vui lòng thử lại sau.',
                'response_data' => null
            ];
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $statusCode = $response ? $response->getStatusCode() : 'unknown';
            $responseBody = $response ? $response->getBody()->getContents() : 'no response';

            // Log::error('TSR API Request Error: ' . $e->getMessage(), [
            //     'request_id' => $cardDeposit->request_id ?? 'unknown',
            //     'error_type' => 'request',
            //     'status_code' => $statusCode,
            //     'response_body' => $responseBody,
            //     'trace' => $e->getTraceAsString()
            // ]);

            return [
                'success' => false,
                'message' => 'Lỗi khi gửi yêu cầu đến hệ thống xử lý thẻ. Vui lòng thử lại sau.',
                'response_data' => null
            ];
        } catch (\Exception $e) {
            Log::error('TSR API General Error: ' . $e->getMessage(), [
                'request_id' => $cardDeposit->request_id ?? 'unknown',
                'error_type' => 'general',
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xử lý thẻ. Vui lòng thử lại sau.',
                'response_data' => null
            ];
        }
    }

    /**
     * Webhook callback từ TheSieuRe
     */
    public function callback(Request $request)
    {
        try {
            $contentType = $request->header('content-type', '');

            if (str_contains($contentType, 'application/json')) {
                $callbackData = $request->json()->all();
                Log::info('TSR Callback received (JSON)', $callbackData);
            } else {
                $callbackData = $request->all();
                Log::info('TSR Callback received (Form)', $callbackData);
            }

            if (!isset($callbackData['callback_sign']) || !isset($callbackData['request_id'])) {
                Log::error('Callback missing required fields', $callbackData);
                return response('Missing required fields', 400);
            }

            $status = $callbackData['status'] ?? null;
            $message = $callbackData['message'] ?? null;
            $requestId = $callbackData['request_id'];
            $transId = $callbackData['trans_id'] ?? null;
            $declaredValue = $callbackData['declared_value'] ?? null;
            $value = $callbackData['value'] ?? null;
            $amount = $callbackData['amount'] ?? null;
            $code = $callbackData['code'] ?? null;
            $serial = $callbackData['serial'] ?? null;
            $telco = $callbackData['telco'] ?? null;
            $callbackSign = $callbackData['callback_sign'];

            if (!$code || !$serial) {
                Log::error('Callback missing code or serial', $callbackData);
                return response('Missing code or serial', 400);
            }

            $expectedSign = md5($this->tsrPartnerKey . $code . $serial);
            if ($callbackSign !== $expectedSign) {
                Log::error('Invalid callback signature', [
                    'expected' => $expectedSign,
                    'received' => $callbackSign,
                    'code' => substr($code, 0, 4) . '****',
                    'serial' => substr($serial, 0, 4) . '****'
                ]);
                return response('Invalid signature', 400);
            }

            // Tìm giao dịch
            $cardDeposit = CardDeposit::where('request_id', $requestId)->first();

            if (!$cardDeposit) {
                Log::error('Card deposit not found', [
                    'request_id' => $requestId,
                    'callback_data' => $callbackData
                ]);
                return response('Card deposit not found', 404);
            }

            if ($cardDeposit->status === CardDeposit::STATUS_SUCCESS) {
                Log::warning('Attempted duplicate callback processing for successful transaction', [
                    'request_id' => $requestId,
                    'current_status' => $cardDeposit->status,
                    'processed_at' => $cardDeposit->processed_at,
                    'user_id' => $cardDeposit->user_id,
                    'coins' => $cardDeposit->coins
                ]);
                return response('Transaction already completed successfully', 200);
            }

            $cardDeposit = CardDeposit::where('request_id', $requestId)
                ->lockForUpdate()
                ->first();

            if (!$cardDeposit) {
                Log::error('Card deposit not found after lock', ['request_id' => $requestId]);
                return response('Card deposit not found', 404);
            }

            // Double check sau khi lock
            if ($cardDeposit->status === CardDeposit::STATUS_SUCCESS) {
                Log::warning('Transaction completed during lock acquisition', [
                    'request_id' => $requestId,
                    'status' => $cardDeposit->status
                ]);
                return response('Transaction already completed', 200);
            }

            DB::beginTransaction();

            $callbackDataToSave = [
                'status' => $status,
                'message' => $message,
                'request_id' => $requestId,
                'trans_id' => $transId,
                'declared_value' => $declaredValue,
                'value' => $value,
                'amount' => $amount,
                'code' => $code,
                'serial' => $serial,
                'telco' => $telco,
                'callback_sign' => $callbackSign,
                'callback_time' => now()->toDateTimeString(),
                'content_type' => $contentType
            ];

            switch ((int)$status) {
                case 1:
                    // Thẻ đúng - thành công
                    $this->processSuccessfulCard($cardDeposit, $callbackDataToSave, $value ?? $amount, 'Nạp thẻ thành công', false);
                    break;

                case 2:
                    // Thành công nhưng sai mệnh giá - áp dụng penalty
                    $note = "Thẻ đúng nhưng sai mệnh giá. Mệnh giá khai báo: " . number_format($declaredValue) . "đ, Mệnh giá thực: " . number_format($value) . "đ";
                    $this->processSuccessfulCard($cardDeposit, $callbackDataToSave, $value ?? $amount, $note, true); // NEW: pass true for wrong amount
                    break;

                case 3:
                    // Thẻ lỗi
                    $cardDeposit->update([
                        'status' => CardDeposit::STATUS_FAILED,
                        'response_data' => $callbackDataToSave,
                        'processed_at' => now(),
                        'note' => $message ?? 'Thẻ không hợp lệ hoặc đã được sử dụng'
                    ]);
                    break;

                case 99:
                    // Đang xử lý
                    $cardDeposit->update([
                        'status' => CardDeposit::STATUS_PROCESSING,
                        'transaction_id' => $transId,
                        'response_data' => $callbackDataToSave,
                        'note' => 'Thẻ đang được xử lý'
                    ]);
                    break;

                default:
                    $cardDeposit->update([
                        'response_data' => $callbackDataToSave,
                        'note' => $message ?? 'Trạng thái không xác định: ' . $status
                    ]);
                    break;
            }

            DB::commit();

            // Log callback
            $logEntry = [
                'time' => date('Y-m-d H:i:s'),
                'status' => $status,
                'message' => $message,
                'request_id' => $requestId,
                'trans_id' => $transId,
                'user_id' => $cardDeposit->user_id,
                'final_status' => $cardDeposit->fresh()->status
            ];

            file_put_contents(
                storage_path('logs/tsr_callback.log'),
                json_encode($logEntry) . "\n",
                FILE_APPEND | LOCK_EX
            );

            return response('OK', 200);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Callback processing error: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Xử lý thẻ thành công với duplicate protection
     */
    private function processSuccessfulCard($cardDeposit, $callbackData, $realAmount, $note, $isWrongAmount = false)
    {
        if ($isWrongAmount) {
            $penaltyPercent = $this->cardWrongAmountPenalty;
            $penaltyAmount = ($realAmount * $penaltyPercent) / 100;
            $amountAfterPenalty = $realAmount - $penaltyAmount;

            $feeAmount = ($amountAfterPenalty * $cardDeposit->fee_percent) / 100;
            $amountAfterFee = $amountAfterPenalty - $feeAmount;
            $coins = floor($amountAfterFee / $this->coinExchangeRate);

            $note .= ". Phí phạt sai mệnh giá: " . number_format($penaltyAmount) . "đ (-{$penaltyPercent}%)";

            Log::info('Wrong amount penalty applied', [
                'request_id' => $callbackData['request_id'],
                'real_amount' => $realAmount,
                'penalty_percent' => $penaltyPercent,
                'penalty_amount' => $penaltyAmount,
                'amount_after_penalty' => $amountAfterPenalty,
                'fee_amount' => $feeAmount,
                'final_coins' => $coins
            ]);
        } else {
            $feeAmount = ($realAmount * $cardDeposit->fee_percent) / 100;
            $amountAfterFee = $realAmount - $feeAmount;
            $coins = floor($amountAfterFee / $this->coinExchangeRate);
            $penaltyAmount = 0;
        }

        $updateData = [
            'status' => CardDeposit::STATUS_SUCCESS,
            'transaction_id' => $callbackData['trans_id'],
            'amount' => $realAmount,
            'coins' => $coins,
            'fee_amount' => $feeAmount,
            'response_data' => $callbackData,
            'processed_at' => now(),
            'note' => $note
        ];

        if ($isWrongAmount) {
            $updateData['penalty_amount'] = $penaltyAmount;
            $updateData['penalty_percent'] = $this->cardWrongAmountPenalty;
        }

        $cardDeposit->update($updateData);


        $user = User::find($cardDeposit->user_id);
        if ($user) {
            // Sử dụng CoinService để ghi lịch sử
            $coinService = new \App\Services\CoinService();
            $description = "Nạp thẻ thành công - Mệnh giá: {$realAmount} VND";
            if ($isWrongAmount) {
                $description .= " (Sai mệnh giá, phạt: {$penaltyAmount} VND)";
            }
            
            $coinService->addCoins(
                $user,
                $coins,
                \App\Models\CoinHistory::TYPE_CARD_DEPOSIT,
                $description,
                $cardDeposit
            );
        } else {
            Log::error('User not found when adding coins', [
                'user_id' => $cardDeposit->user_id,
                'request_id' => $callbackData['request_id']
            ]);
        }
    }

    /**
     * Kiểm tra trạng thái giao dịch
     */
    public function checkStatus($id)
    {
        $cardDeposit = CardDeposit::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$cardDeposit) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy giao dịch'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'status' => $cardDeposit->status,
            'status_text' => $cardDeposit->status_text,
            'coins' => $cardDeposit->coins,
            'note' => $cardDeposit->note,
            'processed_at' => $cardDeposit->processed_at?->format('d/m/Y H:i:s')
        ]);
    }

    public function checkCardForm()
    {
        return view('pages.information.deposit.check_card');
    }

    public function checkCard(Request $request)
    {
        $request->validate([
            'telco' => 'required|string',
            'code' => 'required|string',
            'serial' => 'required|string',
            'amount' => 'required|integer',
            'partner_id' => 'required|string',
            'domain' => 'required|string'
        ]);

        try {
            // Tính signature theo format: md5(partner_key + code + serial)
            $partnerKey = $request->partner_key ?? $this->tsrPartnerKey;
            $signature = md5($partnerKey . $request->code . $request->serial);

            Log::info('Check Card Signature', [
                'partner_key' => $partnerKey,
                'code' => $request->code,
                'serial' => $request->serial,
                'signature' => $signature
            ]);

            // Tạo request ID random
            $requestId = rand(100000, 999999);

            // Setup Guzzle client
            $client = new Client([
                'timeout' => 30,
                'connect_timeout' => 10,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept' => 'application/json, text/plain, */*',
                ]
            ]);

            // Tạo URL từ domain
            $url = 'http://' . $request->domain . '/chargingws/v2';

            // Tạo multipart data
            $multipart = [
                [
                    'name' => 'telco',
                    'contents' => $request->telco
                ],
                [
                    'name' => 'code',
                    'contents' => $request->code
                ],
                [
                    'name' => 'serial',
                    'contents' => $request->serial
                ],
                [
                    'name' => 'amount',
                    'contents' => (string)$request->amount
                ],
                [
                    'name' => 'request_id',
                    'contents' => (string)$requestId
                ],
                [
                    'name' => 'partner_id',
                    'contents' => $request->partner_id
                ],
                [
                    'name' => 'sign',
                    'contents' => $signature
                ],
                [
                    'name' => 'command',
                    'contents' => 'check'
                ]
            ];

            Log::info('Check Card Request', [
                'url' => $url,
                'partner_id' => $request->partner_id,
                'request_id' => $requestId,
                'telco' => $request->telco,
                'amount' => $request->amount,
                'code' => substr($request->code, 0, 4) . '****',
                'serial' => substr($request->serial, 0, 4) . '****',
                'sign' => $signature
            ]);

            // Gửi request
            $response = $client->post($url, [
                'multipart' => $multipart,
                'timeout' => 30,
                'connect_timeout' => 10
            ]);

            // Lấy response
            $responseBody = $response->getBody()->getContents();
            $httpCode = $response->getStatusCode();
            $responseData = json_decode($responseBody, true);

            Log::info('Check Card Response', [
                'request_id' => $requestId,
                'http_code' => $httpCode,
                'response_body' => $responseBody,
                'response_data' => $responseData
            ]);

            return response()->json([
                'success' => true,
                'request_data' => [
                    'url' => $url,
                    'telco' => $request->telco,
                    'code' => substr($request->code, 0, 4) . '****',
                    'serial' => substr($request->serial, 0, 4) . '****',
                    'amount' => $request->amount,
                    'request_id' => $requestId,
                    'partner_id' => $request->partner_id,
                    'signature' => $signature,
                    'command' => 'check'
                ],
                'response_data' => [
                    'http_code' => $httpCode,
                    'raw_response' => $responseBody,
                    'parsed_response' => $responseData,
                    'status' => $responseData['status'] ?? 'unknown',
                    'message' => $responseData['message'] ?? 'No message',
                    'trans_id' => $responseData['trans_id'] ?? null
                ]
            ]);
        } catch (ConnectException $e) {
            Log::error('Check Card Connection Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error_type' => 'connection',
                'message' => 'Không thể kết nối đến server: ' . $e->getMessage()
            ], 500);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $statusCode = $response ? $response->getStatusCode() : 'unknown';
            $responseBody = $response ? $response->getBody()->getContents() : 'no response';

            Log::error('Check Card Request Error: ' . $e->getMessage(), [
                'status_code' => $statusCode,
                'response_body' => $responseBody
            ]);

            return response()->json([
                'success' => false,
                'error_type' => 'request',
                'message' => 'Lỗi request: ' . $e->getMessage(),
                'status_code' => $statusCode,
                'response_body' => $responseBody
            ], 500);
        } catch (\Exception $e) {
            Log::error('Check Card General Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error_type' => 'general',
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }
}
