<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Bank;
use App\Models\Deposit;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class DepositController extends Controller
{
    // Hiển thị trang nạp xu
    public function index()
    {
        $user = Auth::user();
        $banks = Bank::where('status', true)->get();
        $deposits = $user->deposits()->latest()->paginate(10);

        return view('pages.information.deposit.deposit', compact('banks', 'deposits'));
    }

    // Tạo yêu cầu nạp xu mới
    public function store(Request $request)
    {
        $request->validate([
            'bank_id' => 'required|exists:banks,id',
            'amount' => 'required|numeric|min:10000',
            'transaction_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:4096',
        ], [
            'bank_id.required' => 'Vui lòng chọn ngân hàng',
            'bank_id.exists' => 'Ngân hàng không tồn tại',
            'amount.required' => 'Vui lòng nhập số tiền',
            'amount.numeric' => 'Số tiền phải là số',
            'amount.min' => 'Số tiền tối thiểu là 10.000 VNĐ',
            'transaction_image.required' => 'Vui lòng tải lên ảnh chứng minh chuyển khoản',
            'transaction_image.image' => 'File tải lên phải là hình ảnh',
            'transaction_image.mimes' => 'Định dạng hình ảnh phải là jpeg, png, jpg hoặc gif',
            'transaction_image.max' => 'Kích thước hình ảnh không được vượt quá 4MB',
        ]);

        DB::beginTransaction();
        try {
            // Xử lý ảnh chứng minh chuyển khoản
            $imagePath = $this->processAndSaveDepositImage($request->file('transaction_image'));

            // Tính số xu dựa trên số tiền (ví dụ: 1.000 VNĐ = 1 xu)
            $coins = floor($request->amount / 1000);

            // Tạo mã giao dịch ngẫu nhiên
            $transactionCode = 'TX' . strtoupper(Str::random(8)) . Carbon::now()->format('dmy');

            // Tạo yêu cầu nạp xu mới
            Deposit::create([
                'user_id' => Auth::id(),
                'bank_id' => $request->bank_id,
                'transaction_code' => $transactionCode,
                'amount' => $request->amount,
                'coins' => $coins,
                'image' => $imagePath,
                'status' => 'pending',
            ]);

            DB::commit();

            return redirect()->route('user.deposit')
                ->with('success', 'Yêu cầu nạp xu đã được gửi. Chúng tôi sẽ kiểm tra và xử lý trong thời gian sớm nhất.');
        } catch (\Exception $e) {
            DB::rollBack();

            // Xóa ảnh nếu có lỗi xảy ra
            if (isset($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Xử lý hình ảnh chứng minh chuyển khoản
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

    // Xử lý phê duyệt giao dịch (cho admin)
    public function approve(Deposit $deposit)
    {
        if (!Auth::user()->isAdmin()) {
            return redirect()->back()->with('error', 'Bạn không có quyền thực hiện chức năng này.');
        }

        DB::beginTransaction();
        try {
            // Cập nhật trạng thái giao dịch
            $deposit->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => Auth::id(),
            ]);

            // Cộng xu cho người dùng
            $user = $deposit->user;
            $user->coins += $deposit->coins;
            $user->save();

            DB::commit();

            return redirect()->back()->with('success', 'Đã phê duyệt giao dịch và cộng ' . $deposit->coins . ' xu vào tài khoản người dùng.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // Xử lý từ chối giao dịch (cho admin)
    public function reject(Request $request, Deposit $deposit)
    {
        if (!Auth::user()->isAdmin()) {
            return redirect()->back()->with('error', 'Bạn không có quyền thực hiện chức năng này.');
        }

        $request->validate([
            'note' => 'required',
        ], [
            'note.required' => 'Vui lòng nhập lý do từ chối',
        ]);

        try {
            $deposit->update([
                'status' => 'rejected',
                'note' => $request->note,
                'approved_at' => now(),
                'approved_by' => Auth::id(),
            ]);

            return redirect()->back()->with('success', 'Đã từ chối giao dịch.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // Hiển thị trang quản lý giao dịch (cho admin)
    public function adminIndex(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'Bạn không có quyền truy cập trang này.');
        }

        $query = Deposit::with(['user', 'bank']);

        // Lọc theo trạng thái
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Lọc theo ngày
        if ($request->has('date') && !empty($request->date)) {
            $query->whereDate('created_at', $request->date);
        }

        // Lọc theo người dùng
        if ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('user_id', $request->user_id);
        }

        $deposits = $query->latest()->paginate(15)->withQueryString();

        return view('admin.pages.deposits.index', compact('deposits'));
    }
}
