<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\AuthorApplication;
use App\Notifications\AuthorApplicationStatusChanged;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AuthorApplicationController extends Controller
{
    /**
     * Show application form or application status
     */
    public function showApplicationForm()
    {
        $user = Auth::user();
        
        // Check if user already has the author role
        if ($user->role === 'author' || $user->role === 'admin') {
            return redirect()->route('user.author.index');
        }
        
        // Get user's latest application
        $application = $user->latestAuthorApplication();
        
        return view('pages.information.author.author_application', compact('user', 'application'));
    }
    
    /**
     * Submit a new application
     */
    public function submitApplication(Request $request)
    {
        $user = Auth::user();
        
        // Validate if user can submit an application
        if ($user->role === 'author' || $user->role === 'admin') {
            return redirect()->route('user.author.index')
                ->with('error', 'Bạn đã là tác giả, không cần đăng ký.');
        }
        
        // Check if user already has a pending application
        if ($user->hasPendingAuthorApplication()) {
            return redirect()->back()
                ->with('error', 'Bạn đã có một đơn đăng ký đang chờ xét duyệt.');
        }
        
        // Validate request
        $request->validate([
            'facebook_link' => 'required|url|max:255',
            'telegram_link' => 'nullable|url|max:255',
            'other_platform' => 'nullable|string|max:100',
            'other_platform_link' => 'nullable|url|max:255',
            'introduction' => 'nullable|string|min:50|max:1000',
        ], [
            'facebook_link.required' => 'Link Facebook là bắt buộc.',
            'facebook_link.url' => 'Link Facebook không hợp lệ.',
            'facebook_link.max' => 'Link Facebook không được vượt quá 255 ký tự.',
            'telegram_link.url' => 'Link Telegram không hợp lệ.',
            'telegram_link.max' => 'Link Telegram không được vượt quá 255 ký tự.',
            'other_platform_link.url' => 'Link các nền tảng khác không hợp lệ.',
            'other_platform_link.max' => 'Link các nền tảng khác không được vượt quá 255 ký tự.',
            'introduction.min' => 'Phần giới thiệu phải có ít nhất 50 ký tự.',
            'introduction.max' => 'Phần giới thiệu không được vượt quá 1000 ký tự.',
        ]);
        
        try {
            // Create new application
            $application = AuthorApplication::create([
                'user_id' => $user->id,
                'facebook_link' => $request->facebook_link,
                'telegram_link' => $request->telegram_link,
                'other_platform' => $request->other_platform,
                'other_platform_link' => $request->other_platform_link,
                'introduction' => $request->introduction,
                'status' => 'pending',
                'submitted_at' => now(),
            ]);
            
            return redirect()->route('user.author.application')
                ->with('success', 'Đơn đăng ký của bạn đã được gửi thành công và đang chờ xét duyệt.');
        } catch (\Exception $e) {
            Log::error('Error submitting author application: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau.')
                ->withInput();
        }
    }
    
    /**
     * List applications (admin only)
     */
    public function listApplications(Request $request)
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('home')->with('error', 'Bạn không có quyền truy cập trang này.');
        }
        
        $query = AuthorApplication::with('user')->latest();
            
        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default to showing pending applications
            $query->where('status', 'pending');
        }
        
        // Search by user name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $applications = $query->paginate(10);
        
        // Get counts for filter tabs
        $pendingCount = AuthorApplication::where('status', 'pending')->count();
        $approvedCount = AuthorApplication::where('status', 'approved')->count();
        $rejectedCount = AuthorApplication::where('status', 'rejected')->count();
        
        return view('admin.pages.author-applications.index', compact(
            'applications',
            'pendingCount',
            'approvedCount',
            'rejectedCount'
        ));
    }
    
    /**
     * Show application details (admin only)
     */
    public function showApplication(AuthorApplication $application)
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('home')->with('error', 'Bạn không có quyền truy cập trang này.');
        }
        
        return view('admin.pages.author-applications.show', compact('application'));
    }
    
    /**
     * Approve application (admin only)
     */
    public function approveApplication(Request $request, AuthorApplication $application)
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('home')->with('error', 'Bạn không có quyền thực hiện hành động này.');
        }
        
        // Validate request
        $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);
        
        // Check if the application is pending
        if ($application->status !== 'pending') {
            return redirect()->back()->with('error', 'Đơn đăng ký này đã được xử lý.');
        }
        
        DB::beginTransaction();
        try {
            // Update application status
            $application->update([
                'status' => 'approved',
                'admin_note' => $request->admin_note,
                'reviewed_at' => Carbon::now(),
            ]);
            
            // Update user role to author
            $application->user->update([
                'role' => 'author'
            ]);
            
            DB::commit();
            
            // Notify user about application approval
            $application->user->notify(new AuthorApplicationStatusChanged($application));
            
            return redirect()->route('admin.author-applications.index')
                ->with('success', 'Đơn đăng ký đã được phê duyệt và người dùng đã được nâng cấp thành tác giả.');
        } catch (\Exception $e) {
            Log::error('Error approving author application: ' . $e->getMessage());
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi phê duyệt đơn đăng ký');
        }
    }
    
    /**
     * Reject application (admin only)
     */
    public function rejectApplication(Request $request, AuthorApplication $application)
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('home')->with('error', 'Bạn không có quyền thực hiện hành động này.');
        }
        
        // Validate request
        $request->validate([
            'admin_note' => 'required|string|max:1000',
        ], [
            'admin_note.required' => 'Vui lòng cung cấp lý do từ chối đơn đăng ký.'
        ]);
        
        // Check if the application is pending
        if ($application->status !== 'pending') {
            return redirect()->back()->with('error', 'Đơn đăng ký này đã được xử lý.');
        }
        
        try {
            // Update application status
            $application->update([
                'status' => 'rejected',
                'admin_note' => $request->admin_note,
                'reviewed_at' => Carbon::now(),
            ]);
            
            // Notify user about application rejection
            $application->user->notify(new AuthorApplicationStatusChanged($application));
            
            return redirect()->route('admin.author-applications.index')
                ->with('success', 'Đơn đăng ký đã bị từ chối.');
        } catch (\Exception $e) {
            Log::error('Error rejecting author application: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi từ chối đơn đăng ký. Vui lòng thử lại sau.');
        }
    }
}
