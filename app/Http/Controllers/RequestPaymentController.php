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
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class RequestPaymentController extends Controller
{
    // Tạo yêu cầu thanh toán mới
    public function store(Request $request)
    {
        $request->validate([
            'bank_id' => 'required|exists:banks,id',
            'amount' => 'required|numeric|min:10000',
        ], [
            'bank_id.required' => 'Vui lòng chọn ngân hàng',
            'bank_id.exists' => 'Ngân hàng không tồn tại',
            'amount.required' => 'Vui lòng nhập số tiền',
            'amount.numeric' => 'Số tiền phải là số',
            'amount.min' => 'Số tiền tối thiểu là 10.000 VNĐ',
        ]);

        try {
            // Calculate coins and apply discount
            $amount = $request->amount;
            $exchangeRate = Config::getConfig('coin_exchange_rate', 1000);
            $discount = Config::getConfig('bank_transfer_discount', 0);
            
            // Base coins calculation
            $baseCoins = floor($amount / $exchangeRate);
            
            // Calculate bonus coins based on discount
            $bonusCoins = floor($baseCoins * ($discount / 100));
            
            // Total coins
            $totalCoins = $baseCoins + $bonusCoins;
            
            // Create transaction code
            $transactionCode = 'TX' . strtoupper(Str::random(8)) . Carbon::now()->format('dmy');
            
            // Thời hạn của yêu cầu thanh toán (1 giờ)
            $expiredAt = Carbon::now()->addHours(1);
            
            // Tạo yêu cầu thanh toán
            $requestPayment = RequestPayment::create([
                'user_id' => Auth::id(),
                'bank_id' => $request->bank_id,
                'transaction_code' => $transactionCode,
                'amount' => $amount,
                'base_coins' => $baseCoins,
                'bonus_coins' => $bonusCoins,
                'total_coins' => $totalCoins,
                'discount' => $discount,
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
                    'base_coins' => $baseCoins,
                    'bonus_coins' => $bonusCoins,
                    'total_coins' => $totalCoins,
                    'discount' => $discount,
                    'transaction_code' => $transactionCode,
                    'expired_at' => $expiredAt->format('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
            ], 500);
        }
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

        DB::beginTransaction();
        try {
            // Lấy thông tin yêu cầu thanh toán
            $requestPayment = RequestPayment::where('id', $request->request_payment_id)
                ->where('user_id', Auth::id())
                ->where('is_completed', false)
                ->firstOrFail();

            // Kiểm tra hết hạn
            if ($requestPayment->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Yêu cầu thanh toán đã hết hạn. Vui lòng tạo yêu cầu mới.'
                ], 400);
            }

            // Xử lý ảnh chứng minh chuyển khoản
            $imagePath = $this->processAndSaveDepositImage($request->file('transaction_image'));

            // Tạo yêu cầu nạp xu mới
            $deposit = Deposit::create([
                'user_id' => Auth::id(),
                'bank_id' => $requestPayment->bank_id,
                'transaction_code' => $requestPayment->transaction_code,
                'amount' => $requestPayment->amount,
                'coins' => $requestPayment->total_coins,
                'image' => $imagePath,
                'status' => 'pending',
            ]);

            // Đánh dấu yêu cầu thanh toán là đã hoàn thành
            $requestPayment->markAsCompleted($deposit->id);

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

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
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
                $query->where('is_completed', false)->where(function($q) {
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
