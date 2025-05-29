<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoinTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CoinController extends Controller
{
    /**
     * Display a listing of users for coin management.
     */
    public function index(Request $request)
    {
        $query = User::query();
        
        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $users = $query->orderBy('name')->paginate(15);
        
        return view('admin.pages.coins.index', compact('users'));
    }
    
    /**
     * Show form to add/subtract coins from a user
     */
    public function create($userId)
    {
        $user = User::findOrFail($userId);
        return view('admin.pages.coins.create', compact('user'));
    }
    
    /**
     * Process the coin transaction
     */
    public function store(Request $request, $userId)
    {
        $request->validate([
            'amount' => 'required|integer|min:1',
            'type' => 'required|in:add,subtract',
            'note' => 'nullable|string|max:500',
        ], [
            'amount.required' => 'Vui lòng nhập số xu',
            'amount.integer' => 'Số xu phải là số nguyên',
            'amount.min' => 'Số xu phải lớn hơn 0',
            'type.required' => 'Vui lòng chọn loại giao dịch',
            'type.in' => 'Loại giao dịch không hợp lệ',
            'note.max' => 'Ghi chú không được vượt quá 500 ký tự',
        ]);
        
        $user = User::findOrFail($userId);
        $admin = auth()->user();
        
        DB::beginTransaction();
        
        try {
            // Create the transaction record
            $transaction = CoinTransaction::create([
                'user_id' => $user->id,
                'admin_id' => $admin->id,
                'amount' => $request->amount,
                'type' => $request->type,
                'note' => $request->note,
            ]);
            
            // Update user's coins
            if ($request->type === 'add') {
                $user->coins += $request->amount;
            } else {
                // Check if user has enough coins
                if ($user->coins < $request->amount) {
                    return redirect()->back()->with('error', 'Người dùng không đủ xu để trừ');
                }
                
                $user->coins -= $request->amount;
            }
            
            $user->save();
            
            DB::commit();
            
            return redirect()->route('coins.index')
                ->with('success', 'Giao dịch xu thành công');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing coin transaction: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi xử lý giao dịch xu');
        }
    }
} 