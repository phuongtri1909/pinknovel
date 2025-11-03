<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\BankAuto;
use App\Models\Config;
use App\Models\BankAutoDeposit;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Services\CoinService;

class BankAutoController extends Controller
{
    public $coinBankAutoPercent;
    public $minBankAutoDepositAmount;
    public $coinExchangeRate;

    public function __construct()
    {
        $this->coinBankAutoPercent = Config::getConfig('coin_bank_auto_percentage', 20);
        $this->minBankAutoDepositAmount = Config::getConfig('min_bank_auto_deposit_amount', 100000);
        $this->coinExchangeRate = Config::getConfig('coin_exchange_rate', 100);
    }

    public function index()
    {
        $user = Auth::user();
        $banks = BankAuto::where('status', true)->get();
        
        $coinExchangeRate = $this->coinExchangeRate;
        $coinBankAutoPercent = $this->coinBankAutoPercent;
        $minBankAutoDepositAmount = $this->minBankAutoDepositAmount;

        return view('pages.information.deposit.bank_auto', compact(
            'banks', 
            'coinExchangeRate',
            'coinBankAutoPercent',
            'minBankAutoDepositAmount',
        ));
    }

    /**
     * Tính toán số xu nhận được
     */
    public function calculateCoins($amount)
    {
        $feeAmount = ($amount * $this->coinBankAutoPercent) / 100;
        $amountAfterFee = $amount - $feeAmount;
        $coins = floor($amountAfterFee / $this->coinExchangeRate);
        return [
            'coins' => $coins,
            'fee_amount' => $feeAmount,
        ];
    }

    /**
     * API endpoint để tính toán preview coins
     */
    public function calculatePreview(Request $request)
    {
        $amount = $request->input('amount', 0);
        
        if ($amount < $this->minBankAutoDepositAmount) {
            return response()->json([
                'success' => false,
                'message' => 'Số tiền tối thiểu là ' . number_format($this->minBankAutoDepositAmount) . ' VNĐ'
            ]);
        }
        
        
        $calculation = $this->calculateCoins($amount);
        
        return response()->json([
            'success' => true,
            'data' => $calculation
        ]);
    }

    /**
     * Tạo giao dịch bank auto
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:' . $this->minBankAutoDepositAmount,
            'bank_id' => 'required|exists:bank_autos,id'
        ]);

        $amount = $request->input('amount');
        $bankId = $request->input('bank_id');
        
        // Tính toán coins
        $calculation = $this->calculateCoins($amount);
        
        $transactionCode = 'PINKNOVEL' . time() . strtoupper(Str::random(6)) . Auth::id();
        
        DB::beginTransaction();
        try {
            $bankAutoDeposit = BankAutoDeposit::create([
                'user_id' => Auth::id(),
                'bank_id' => $bankId,
                'transaction_code' => $transactionCode,
                'amount' => $amount,
                'coins' => $calculation['coins'],
                'fee_amount' => $calculation['fee_amount'],
                'status' => BankAutoDeposit::STATUS_PENDING
            ]);

            DB::commit();

            $bank = BankAuto::find($bankId);
            $qrCodeData = null;
            if ($bank) {
                $qrCodeData = $this->generateBankQRCode($bank, $transactionCode, $amount);
            }

            $bankInfo = $this->getBankInfo($bankId);
            if ($qrCodeData) {
                $bankInfo['qr_code'] = $qrCodeData;
            }

            return response()->json([
                'success' => true,
                'transaction_code' => $transactionCode,
                'amount' => $amount,
                'coins' => $calculation['coins'],
                'bank_info' => $bankInfo,
                'message' => 'Vui lòng chuyển khoản theo thông tin bên dưới'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating bank auto deposit: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo giao dịch'
            ]);
        }
    }

    /**
     * Lấy thông tin ngân hàng để hiển thị cho user
     */
    private function getBankInfo($bankId)
    {
        $bank = BankAuto::find($bankId);
        
        if (!$bank) {
            return null;
        }
        
        return [
            'name' => $bank->name,
            'code' => $bank->code,
            'account_number' => $bank->account_number ?? 'Chưa cấu hình',
            'account_name' => $bank->account_name ?? 'Chưa cấu hình',
            'logo' => $bank->logo ? Storage::url($bank->logo) : null,
            'qr_code' => $bank->qr_code ? Storage::url($bank->qr_code) : null,
        ];
    }

    /**
     * Callback từ Casso Webhook v2 khi có giao dịch mới
     */
    public function callback(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Casso-Signature');
        
        if (!$signature) {
            Log::warning('Thiếu chữ ký Casso trong header');
            return response()->json(['success' => false, 'message' => 'Thiếu chữ ký Casso'], 401);
        }
        
        if (!$this->verifyCassoSignature($payload, $signature)) {
            Log::warning('Chữ ký Casso không hợp lệ', [
                'signature' => $signature,
                'payload_preview' => substr($payload, 0, 100)
            ]);
            return response()->json(['success' => false, 'message' => 'Chữ ký Casso không hợp lệ'], 401);
        }
        
        // Parse JSON payload
        $data = json_decode($payload, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('JSON payload không hợp lệ', ['error' => json_last_error_msg()]);
            return response()->json(['success' => false, 'message' => 'JSON payload không hợp lệ'], 400);
        }
        
        // Casso Webhook v2 format
        $transactionId = $data['data']['id'] ?? null;
        $reference = $data['data']['reference'] ?? null;
        $description = $data['data']['description'] ?? '';
        $amount = $data['data']['amount'] ?? 0;
        $accountNumber = $data['data']['accountNumber'] ?? '';
        $bankName = $data['data']['bankName'] ?? '';
        $transactionDateTime = $data['data']['transactionDateTime'] ?? null;
        
        if (!$transactionId) {
            Log::warning('Thiếu ID giao dịch trong webhook Casso', ['data' => $data]);
            return response()->json(['success' => false, 'message' => 'Thiếu ID giao dịch'], 400);
        }
        
        $existingDeposit = BankAutoDeposit::where('casso_transaction_id', $transactionId)
            ->where('status', BankAutoDeposit::STATUS_SUCCESS)
            ->first();
            
        if ($existingDeposit) {
            return response()->json(['success' => true, 'message' => 'Giao dịch đã được xử lý trước đó'], 200);
        }
        
        DB::beginTransaction();
        try {
            $transactionCode = null;
            
            if (preg_match_all('/(PINKNOVEL[a-zA-Z0-9]{14,})/', $description, $matches)) {
                $transactionCode = $matches[1][0];
                
            }
            
            $deposit = null;
            if ($transactionCode) {
                $deposit = BankAutoDeposit::where('transaction_code', $transactionCode)
                    ->where('status', BankAutoDeposit::STATUS_PENDING)
                    ->first();
            }
                
            if (!$deposit) {
                Log::warning('Bank auto deposit not found', [
                    'reference' => $reference,
                    'transaction_id' => $transactionId,
                    'description' => $description,
                    'extracted_code' => $transactionCode
                ]);
                return response()->json(['success' => false, 'message' => 'Giao dịch không tồn tại'], 404);
            }
            
            if ($amount != $deposit->amount) {
                Log::warning('Số tiền nhận được không khớp', [
                    'expected' => $deposit->amount,
                    'received' => $amount,
                    'reference' => $reference,
                    'description' => $description
                ]);
                
                $deposit->update([
                    'status' => BankAutoDeposit::STATUS_FAILED,
                    'note' => 'Số tiền nhận được không khớp',
                    'casso_response' => $data
                ]);
                
                DB::commit();
                return response()->json(['success' => false, 'message' => 'Số tiền không khớp'], 400);
            }
            
            $deposit->update([
                'status' => BankAutoDeposit::STATUS_SUCCESS,
                'processed_at' => now(),
                'casso_transaction_id' => $transactionId,
                'casso_response' => $data
            ]);
            
            $this->broadcastTransactionUpdate($transactionCode, 'success', $deposit);
            
            $user = $deposit->user;
            if ($user) {
                $coinService = new CoinService();
                $coinService->addCoins(
                    $user,
                    $deposit->coins,
                    \App\Models\CoinHistory::TYPE_BANK_AUTO_DEPOSIT,
                    "Nạp bank auto thành công - Số tiền: " . number_format($deposit->amount) . " VND -  Mã giao dịch: {$transactionCode}",
                    $deposit
                );
                
            }
            
            DB::commit();
            
            return response()->json(['success' => true], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi xử lý callback giao dịch bank auto: ' . $e->getMessage(), [
                'transaction_id' => $transactionId,
                'reference' => $reference,
                'data' => $data,
                'trace' => $e->getTraceAsString(),
                'description' => $description
            ]);
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra khi xử lý'], 500);
        }
    }


    /**
     * Verify signature từ Casso Webhook v2
     */
    private function verifyCassoSignature($payload, $signature)
    {
        $secret = config('services.casso.webhook_secret');
        
        if (!$secret) {
            Log::error('Secret webhook Casso không được cấu hình');
            return false;
        }
        
        if (!preg_match('/t=(\d+),v1=(.+)/', $signature, $matches)) {
            Log::warning('Invalid signature format', ['signature' => $signature]);
            return false;
        }
        
        $timestamp = $matches[1];
        $receivedSignature = $matches[2];
        
        $currentTime = time() * 1000;
        $signatureTime = (int)$timestamp;
        $timeDiff = abs($currentTime - $signatureTime);
        
        if ($timeDiff > 300000) {
            Log::warning('Chữ ký timestamp quá cũ', [
                'current_time' => $currentTime,
                'signature_time' => $signatureTime,
                'time_diff' => $timeDiff
            ]);
            return false;
        }
        
        $data = json_decode($payload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('JSON payload không hợp lệ cho việc xác thực chữ ký', ['error' => json_last_error_msg()]);
            return false;
        }
        
        $sortedData = $this->sortDataByKey($data);
        
        $messageToSign = $timestamp . '.' . json_encode($sortedData, JSON_UNESCAPED_SLASHES);
        
        $expectedSignature = hash_hmac('sha512', $messageToSign, $secret);
        
        return hash_equals($expectedSignature, $receivedSignature);
    }
    
    /**
     * Sắp xếp dữ liệu theo key
     */
    private function sortDataByKey($data)
    {
        if (!is_array($data)) {
            return $data;
        }
        
        $sortedData = [];
        $keys = array_keys($data);
        sort($keys);
        
        foreach ($keys as $key) {
            if (is_array($data[$key])) {
                $sortedData[$key] = $this->sortDataByKey($data[$key]);
            } else {
                $sortedData[$key] = $data[$key];
            }
        }
        
        return $sortedData;
    }
    
    /**
     * Broadcast transaction update via SSE
     */
    private function broadcastTransactionUpdate($transactionCode, $status, $deposit)
    {
        $sseData = [
            'transaction_code' => $transactionCode,
            'status' => $status,
            'deposit_id' => $deposit->id,
            'amount' => $deposit->amount,
            'coins' => $deposit->coins,
            'timestamp' => now()->toISOString(),
        ];
        
        $filename = storage_path('app/sse_transaction_' . $transactionCode . '.json');
        file_put_contents($filename, json_encode($sseData));
        
    }
    
    /**
     * Generate QR code for bank using VietQR API
     */
    private function generateBankQRCode($bank, $transactionCode, $amount)
    {
        try {
            $accountNo = $bank->account_number;
            $accountName = $bank->account_name;
            $bankCode = $bank->code;
            $description = $transactionCode;
            
            $qrData = $this->callVietQRAPI($bankCode, $accountNo, $accountName, $amount, $description);
            
            if ($qrData) {
                return $qrData;
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Error generating QR code: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Call VietQR API to generate QR code
     */
    private function callVietQRAPI($bankCode, $accountNo, $accountName, $amount, $description)
    {
        try {
            $url = "https://img.vietqr.io/image/{$bankCode}-{$accountNo}-compact2.jpg";
            
            $params = [
                'amount' => (int)$amount,
                'addInfo' => $description,
                'accountName' => $accountName
            ];
            
            $queryString = http_build_query($params);
            $fullUrl = $url . '?' . $queryString;
            
            $ch = curl_init($fullUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $imageData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200 && !empty($imageData)) {
                $base64 = base64_encode($imageData);
                return 'data:image/jpeg;base64,' . $base64;
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('VietQR API Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Endpoint SSE để lắng nghe cập nhật giao dịch
     */
    public function sseTransactionUpdates(Request $request)
    {
        $transactionCode = $request->get('transaction_code');
        
        if (!$transactionCode) {
            return response('Missing transaction_code', 400);
        }
        
        return response()->stream(function () use ($transactionCode) {
            $filename = storage_path('app/sse_transaction_' . $transactionCode . '.json');
            $lastModified = 0;
            
            while (true) {
                if (file_exists($filename)) {
                    $currentModified = filemtime($filename);
                    
                    if ($currentModified > $lastModified) {
                        $data = json_decode(file_get_contents($filename), true);
                        
                        echo "data: " . json_encode($data) . "\n\n";
                        
                        $lastModified = $currentModified;
                        
                        if ($data['status'] === 'success') {
                            echo "data: " . json_encode(['type' => 'close']) . "\n\n";
                            break;
                        }
                    }
                }
                
                sleep(1);
                
                if (connection_aborted()) {
                    break;
                }
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Cache-Control',
        ]);
    }
}
