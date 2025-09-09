<?php

namespace App\Http\Controllers;

use App\Models\Config;
use App\Models\WithdrawalRequest;
use App\Models\CoinTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    protected $feePercentage;
    protected $minWithdrawalAmount;
    protected $feeThresholdAmount;
    protected $coinExchangeRate;

    public function __construct()
    {
        $this->feePercentage = Config::getConfig('withdrawal_coins_percentage', 5);
        $this->minWithdrawalAmount = Config::getConfig('min_withdrawal_amount', 2000);
        $this->feeThresholdAmount = Config::getConfig('fee_threshold_amount', 10000);
        $this->coinExchangeRate = Config::getConfig('coin_exchange_rate', 100);
    }

    /**
     * Display a listing of the user's withdrawal requests.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $withdrawalRequests = auth()->user()->withdrawalRequests()
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('pages.information.withdrawals.index', compact('withdrawalRequests'));
    }
    
    /**
     * Show the form for creating a new withdrawal request.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $user = auth()->user();
        
        // Check if user is an author
        if ($user->role !== 'author' && $user->role !== 'admin') {
            return redirect()->route('user.profile')
                ->with('error', 'Chỉ tác giả mới có thể rút xu.');
        }
        
        $feePercentage = $this->feePercentage;
        $minWithdrawalAmount = $this->minWithdrawalAmount;
        $feeThresholdAmount = $this->feeThresholdAmount;
        $coinExchangeRate = $this->coinExchangeRate;
        
        return view('pages.information.withdrawals.create', compact(
            'feePercentage', 
            'minWithdrawalAmount', 
            'feeThresholdAmount',
            'coinExchangeRate'
        ));
    }
    
    /**
     * Store a newly created withdrawal request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        $feePercentage = $this->feePercentage;
        $minWithdrawalAmount = $this->minWithdrawalAmount;
        $feeThresholdAmount = $this->feeThresholdAmount;
        $coinExchangeRate = $this->coinExchangeRate;

        // Check if user is an author
        if ($user->role !== 'author' && $user->role !== 'admin') {
            return redirect()->back()
                ->with('error', 'Chỉ tác giả mới có thể rút xu.');
        }
        
        // Validate request data
        $validated = $request->validate([
            'coins' => 'required|integer|min:' . $minWithdrawalAmount,
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'bank_name' => 'required|nullable|string|max:255',
            'additional_info' => 'nullable|string|max:1000',
        ]);
        
        // Check if user has enough coins
        if ($user->coins < $validated['coins']) {
            return back()->withErrors([
                'coins' => 'Số dư xu không đủ để thực hiện giao dịch này.'
            ])->withInput();
        }
        
        // Check if withdrawal amount meets minimum requirement
        if ($validated['coins'] < $minWithdrawalAmount) {
            return back()->withErrors([
                'coins' => 'Số xu rút tối thiểu là ' . $minWithdrawalAmount . ' xu.'
            ])->withInput();
        }
        
        // Tính phí rút xu nếu dưới ngưỡng 
        $fee = 0;
        if ($validated['coins'] < $feeThresholdAmount) {
            $fee = round(($validated['coins'] * $feePercentage) / 100);
        }
        
        // Tính số xu thực rút
        $netCoinAmount = $validated['coins'] - $fee;
        
        // Convert coins to VND
        $vndAmount = $netCoinAmount * $coinExchangeRate;
        
        // Prepare payment information
        $paymentInfo = [
            'account_name' => $validated['account_name'],
            'account_number' => $validated['account_number'],
            'bank_name' => $validated['bank_name'],
            'additional_info' => $validated['additional_info'] ?? null,
            'vnd_amount' => $vndAmount,
            'exchange_rate' => $coinExchangeRate
        ];
        
        // Begin transaction
        DB::beginTransaction();
        
        try {
            // Create withdrawal request
            $withdrawalRequest = WithdrawalRequest::create([
                'user_id' => $user->id,
                'coins' => $validated['coins'],
                'fee' => $fee,
                'net_amount' => $netCoinAmount,
                'payment_info' => $paymentInfo,
                'status' => WithdrawalRequest::STATUS_PENDING,
            ]);
            
            // Deduct coins from user's balance
            $user->update([
                'coins' => $user->coins - $validated['coins']
            ]);
            
            // Commit transaction
            DB::commit();
            
            return redirect()->route('user.withdrawals.index')
                ->with('success', 'Yêu cầu rút xu đã được gửi thành công và đang chờ xử lý.');
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            
            return back()->withErrors([
                'error' => 'Đã xảy ra lỗi khi xử lý yêu cầu rút xu. Vui lòng thử lại sau.'
            ])->withInput();
        }
    }
    
    /**
     * Display a listing of all withdrawal requests (admin).
     *
     * @return \Illuminate\View\View
     */
    public function adminIndex(Request $request)
    {
        $status = $request->get('status', 'pending');
        
        $query = WithdrawalRequest::with('user')
            ->orderBy('created_at', 'desc');
            
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        $withdrawalRequests = $query->paginate(15);
        
        return view('admin.pages.withdrawals.index', compact('withdrawalRequests', 'status'));
    }
    
    /**
     * Show a withdrawal request (admin).
     *
     * @param  \App\Models\WithdrawalRequest  $withdrawal
     * @return \Illuminate\View\View
     */
    public function adminShow(WithdrawalRequest $withdrawal)
    {
        $withdrawal->load('user');

        $feePercentage = $this->feePercentage;
        $minWithdrawalAmount = $this->minWithdrawalAmount;
        $feeThresholdAmount = $this->feeThresholdAmount;
        $coinExchangeRate = $this->coinExchangeRate;
        
        return view('admin.pages.withdrawals.show', compact('withdrawal', 'feePercentage', 'minWithdrawalAmount', 'feeThresholdAmount', 'coinExchangeRate'));
    }
    
    /**
     * Approve a withdrawal request (admin).
     *
     * @param  \App\Models\WithdrawalRequest  $withdrawal
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(WithdrawalRequest $withdrawal)
    {
        // Check if request is already processed
        if ($withdrawal->status !== WithdrawalRequest::STATUS_PENDING) {
            return back()->with('error', 'Yêu cầu rút xu này đã được xử lý trước đó.');
        }
        
        // Update withdrawal request
        $withdrawal->update([
            'status' => WithdrawalRequest::STATUS_APPROVED,
            'processed_at' => now(),
            'processed_by' => auth()->id()
        ]);
        
        return redirect()->route('admin.withdrawals.index')
            ->with('success', 'Yêu cầu rút xu đã được phê duyệt thành công.');
    }
    
    /**
     * Reject a withdrawal request (admin).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WithdrawalRequest  $withdrawal
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(Request $request, WithdrawalRequest $withdrawal)
    {
        // Validate request data
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);
        
        // Check if request is already processed
        if ($withdrawal->status !== WithdrawalRequest::STATUS_PENDING) {
            return back()->with('error', 'Yêu cầu rút xu này đã được xử lý trước đó.');
        }
        
        // Begin transaction
        DB::beginTransaction();
        
        try {
            // Update withdrawal request
            $withdrawal->update([
                'status' => WithdrawalRequest::STATUS_REJECTED,
                'rejection_reason' => $validated['rejection_reason'],
                'processed_at' => now(),
                'processed_by' => auth()->id()
            ]);
            
            // Refund coins to user using CoinService
            $user = $withdrawal->user;
            $coinService = new \App\Services\CoinService();
            $coinService->addCoins(
                $user,
                $withdrawal->coins,
                \App\Models\CoinHistory::TYPE_WITHDRAWAL_REFUND,
                "Hoàn tiền rút xu - Lý do: {$request->note}",
                $withdrawal
            );
            
            // Commit transaction
            DB::commit();
            
            return redirect()->route('admin.withdrawals.index')
                ->with('success', 'Yêu cầu rút xu đã bị từ chối và xu đã được hoàn trả cho người dùng.');
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            
            return back()->with('error', 'Đã xảy ra lỗi khi xử lý yêu cầu rút xu. Vui lòng thử lại sau.');
        }
    }
} 