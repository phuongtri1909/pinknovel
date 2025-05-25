<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Bank;
use App\Models\Config;
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
        $deposits = Deposit::where('user_id', $user->id)->latest()->paginate(10);
        
        // Get discount rates and exchange rate from config
        $bankTransferDiscount = Config::getConfig('bank_transfer_discount', 0);
        $cardPaymentDiscount = Config::getConfig('card_payment_discount', 10);
        $coinExchangeRate = Config::getConfig('coin_exchange_rate', 1000);

        return view('pages.information.deposit.deposit', compact(
            'banks', 
            'deposits',
            'bankTransferDiscount',
            'cardPaymentDiscount',
            'coinExchangeRate'
        ));
    }

    // Xử lý phê duyệt giao dịch (cho admin)
    public function approve(Deposit $deposit)
    {
        if (!auth()->user() || auth()->user()->role !== 'admin') {
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
        if (!auth()->user() || auth()->user()->role !== 'admin') {
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
        if (!auth()->user() || auth()->user()->role !== 'admin') {
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

        $deposits = $query->latest()->paginate(15);

        return view('admin.pages.deposits.index', compact('deposits'));
    }
}
