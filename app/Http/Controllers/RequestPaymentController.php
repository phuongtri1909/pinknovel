<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Bank;
use App\Models\Config;
use App\Models\Deposit;
use App\Models\RequestPayment;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\DepositNotificationMail;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class RequestPaymentController extends Controller
{

    public $coinBankPercent;
    public $coinPayPalPercent;
    public $coinCardPercent;

    public $coinExchangeRate;
    public $coinPayPalRate;

    public function __construct()
    {
        $this->coinBankPercent = Config::getConfig('coin_bank_percent', 15);
        $this->coinPayPalPercent = Config::getConfig('coin_paypal_percent', 0);
        $this->coinCardPercent = Config::getConfig('coin_card_percent', 30);

        $this->coinExchangeRate = Config::getConfig('coin_exchange_rate', 100);
        $this->coinPayPalRate = Config::getConfig('coin_paypal_rate', 20000);
    }

    // Tạo yêu cầu thanh toán mới
    public function store(Request $request)
    {
        $request->validate([
            'bank_id' => 'required|exists:banks,id',
            'amount' => [
                'required',
                'numeric',
                'min:50000',
                function ($attribute, $value, $fail) {
                    if ($value % 10000 !== 0) {
                        $fail('Số tiền phải là bội số của 10.000 VNĐ (ví dụ: 50.000, 60.000, 70.000...)');
                    }
                },
            ],
        ], [
            'bank_id.required' => 'Vui lòng chọn ngân hàng',
            'bank_id.exists' => 'Ngân hàng không tồn tại',
            'amount.required' => 'Vui lòng nhập số tiền',
            'amount.numeric' => 'Số tiền phải là số',
            'amount.min' => 'Số tiền tối thiểu là 50.000 VNĐ',
        ]);

        try {
            // Calculate coins and apply discount
            $amount = $request->amount;

            // Apply bank fee
            $feeAmount = ($amount * $this->coinBankPercent) / 100;

            // Số tiền sau khi trừ phí
            $amountAfterFee = $amount - $feeAmount;

            // Số xu sau khi trừ phí
            $coins = floor($amountAfterFee / $this->coinExchangeRate);

            // Create transaction code
            $transactionCode = $this->generateUniqueTransactionCode();

            // Thời hạn của yêu cầu thanh toán (1 giờ)
            $expiredAt = Carbon::now()->addHour();

            // Tạo yêu cầu thanh toán
            $requestPayment = RequestPayment::create([
                'user_id' => Auth::id(),
                'bank_id' => $request->bank_id,
                'transaction_code' => $transactionCode,
                'amount' => $amount,
                'coins' => $coins,
                'fee' => $feeAmount,
                'expired_at' => $expiredAt
            ]);

            // Lấy thông tin bank để trả về
            $bank = Bank::findOrFail($request->bank_id);

            return response()->json([
                'success' => true,
                'request_payment_id' => $requestPayment->id,
                'bank' => [
                    'id' => $bank->id,
                    'name' => $bank->name,
                    'code' => $bank->code,
                    'account_number' => $bank->account_number,
                    'account_name' => $bank->account_name,
                    'qr_code' => $bank->qr_code ? Storage::url($bank->qr_code) : null,
                ],
                'payment' => [
                    'amount' => $amount,
                    'coins' => $coins,
                    'fee' => $feeAmount,
                    'transaction_code' => $transactionCode,
                    'expired_at' => $expiredAt->toIso8601String()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating request payment: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi tạo yêu cầu thanh toán'
            ], 500);
        }
    }

    private function generateUniqueTransactionCode()
    {
        $maxAttempts = 10;
        $attempt = 0;

        do {
            $attempt++;
            $userId = Auth::id();

            $shortTimestamp = (time() % 100000);

            $timestampBase36 = strtoupper(base_convert($shortTimestamp, 10, 36));
            $random = strtoupper(substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 2));

            $transactionCode = "P{$userId}{$timestampBase36}{$random}";

            $exists = RequestPayment::where('transaction_code', $transactionCode)->exists() ||
                Deposit::where('transaction_code', $transactionCode)->exists();
        } while ($exists && $attempt < $maxAttempts);

        if ($exists) {
            $shortUuid = strtoupper(substr(str_replace('-', '', Str::uuid()), 0, 8));
            $transactionCode = "P{$userId}{$shortUuid}";
        }

        return $transactionCode;
    }

    // Xác nhận đã chuyển khoản và tải lên chứng từ
    public function confirm(Request $request)
    {
        $request->validate([
            'request_payment_id' => 'required|exists:request_payments,id',
            'transaction_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:4096',
        ], [
            'request_payment_id.required' => 'Mã yêu cầu thanh toán không hợp lệ',
            'request_payment_id.exists' => 'Yêu cầu thanh toán không tồn tại',
            'transaction_image.required' => 'Vui lòng tải lên ảnh chứng minh chuyển khoản',
            'transaction_image.image' => 'File tải lên phải là hình ảnh',
            'transaction_image.mimes' => 'Định dạng hình ảnh phải là jpeg, png, jpg hoặc gif',
            'transaction_image.max' => 'Kích thước hình ảnh không được vượt quá 4MB',
        ]);

        $requestPayment = RequestPayment::findOrFail($request->request_payment_id);

        if ($requestPayment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xác nhận yêu cầu thanh toán này.'
            ], 403);
        }

        if ($requestPayment->is_completed && $requestPayment->deposit_id) {
            return response()->json([
                'success' => false,
                'message' => 'Yêu cầu thanh toán đã được xử lý.'
            ], 400);
        }

        if ($requestPayment->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Yêu cầu thanh toán đã hết hạn. Vui lòng tạo yêu cầu mới.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Xử lý ảnh chứng minh chuyển khoản
            $imagePath = $this->processAndSaveDepositImage($request->file('transaction_image'));

            // Tạo giao dịch nạp xu mới
            $deposit = Deposit::create([
                'user_id' => Auth::id(),
                'bank_id' => $requestPayment->bank_id,
                'transaction_code' => $requestPayment->transaction_code,
                'amount' => $requestPayment->amount,
                'coins' => $requestPayment->coins,
                'fee' => $requestPayment->fee,
                'image' => $imagePath,
                'status' => 'pending',
            ]);

            // Load relationships for email
            $deposit->load(['user', 'bank']);

            // Đánh dấu yêu cầu thanh toán là đã hoàn thành
            $requestPayment->markAsCompleted($deposit->id);

            // Gửi email thông báo cho super admin
            $this->sendDepositNotificationToAdmin($deposit);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Yêu cầu nạp xu đã được gửi. Chúng tôi sẽ kiểm tra và xử lý trong thời gian sớm nhất.',
                'deposit_id' => $deposit->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            // Xóa ảnh nếu có lỗi xảy ra
            if (isset($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            Log::error('Error in deposit confirmation: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xác nhận yêu cầu nạp xu. Vui lòng thử lại sau.'
            ], 500);
        }
    }

    /**
     * Gửi email thông báo yêu cầu nạp tiền cho super admin
     */
    private function sendDepositNotificationToAdmin(Deposit $deposit)
    {
        try {
            // Lấy email super admin từ .env
            $superAdminEmails = env('SUPER_ADMIN_EMAILS');

            if (empty($superAdminEmails)) {
                Log::warning('SUPER_ADMIN_EMAILS not configured in .env file');
                return;
            }

            // Chuyển đổi string thành array nếu có nhiều email
            $emailArray = explode(',', $superAdminEmails);
            $emailArray = array_map('trim', $emailArray); // Loại bỏ khoảng trắng
            $emailArray = array_filter($emailArray); // Loại bỏ email rỗng

            foreach ($emailArray as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    Mail::to($email)->send(new DepositNotificationMail($deposit));
                } else {
                    Log::warning("Invalid email address in SUPER_ADMIN_EMAILS: {$email}");
                }
            }
        } catch (\Exception $e) {
            // Log lỗi nhưng không throw exception để không ảnh hưởng đến luồng chính
            Log::error('Failed to send deposit notification email: ' . $e->getMessage());
        }
    }

    // Hiển thị danh sách yêu cầu thanh toán cho admin
    public function adminIndex(Request $request)
    {
        if (!auth()->user() || auth()->user()->role !== 'admin') {
            return redirect()->route('home')->with('error', 'Bạn không có quyền truy cập trang này.');
        }

        $query = RequestPayment::with(['user', 'bank', 'deposit']);

        // Lọc theo trạng thái
        if ($request->has('status') && !empty($request->status)) {
            if ($request->status === 'expired') {
                $query->whereNotNull('expired_at')->where('expired_at', '<', now())->where('is_completed', false);
            } elseif ($request->status === 'pending') {
                $query->where('is_completed', false)->where(function ($q) {
                    $q->whereNull('expired_at')->orWhere('expired_at', '>=', now());
                });
            } elseif ($request->status === 'completed') {
                $query->where('is_completed', true);
            }
        }

        // Lọc theo ngày
        if ($request->has('date') && !empty($request->date)) {
            $query->whereDate('created_at', $request->date);
        }

        // Lọc theo người dùng
        if ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('user_id', $request->user_id);
        }

        $requestPayments = $query->latest()->paginate(15);

        return view('admin.pages.deposits.request_payment.index', compact('requestPayments'));
    }

    // Xóa yêu cầu thanh toán đã hết hạn
    public function deleteExpired()
    {
        if (!auth()->user() || auth()->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện chức năng này.'
            ], 403);
        }

        $count = RequestPayment::whereNotNull('expired_at')
            ->where('expired_at', '<', now())
            ->where('is_completed', false)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "Đã xóa {$count} yêu cầu thanh toán hết hạn."
        ]);
    }

    // Xử lý hình ảnh chứng minh chuyển khoản (giống với DepositController)
    private function processAndSaveDepositImage($imageFile)
    {
        $now = Carbon::now();
        $yearMonth = $now->format('Y/m');
        $timestamp = $now->format('YmdHis');
        $randomString = Str::random(8);
        $fileName = "deposit_{$timestamp}_{$randomString}";

        // Tạo thư mục nếu chưa tồn tại
        Storage::disk('public')->makeDirectory("deposits/{$yearMonth}");

        // Xử lý hình ảnh
        $image = Image::make($imageFile);
        $image->resize(800, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $image->encode('webp', 85);

        // Lưu hình ảnh
        Storage::disk('public')->put(
            "deposits/{$yearMonth}/{$fileName}.webp",
            $image->stream()
        );

        return "deposits/{$yearMonth}/{$fileName}.webp";
    }
}
