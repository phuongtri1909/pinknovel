<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Story;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Notifications\StoryStatusChanged;

class StoryReviewController extends Controller
{
    /**
     * Display a listing of stories pending review
     */
    public function index(Request $request)
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('home')->with('error', 'Bạn không có quyền truy cập trang này.');
        }
        
        $query = Story::with(['user', 'categories'])
            ->latest();
            
        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default to showing pending requests
            $query->where('status', 'pending');
        }
        
        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Author filter
        if ($request->filled('author_id')) {
            $query->where('user_id', $request->author_id);
        }
        
        // Submission date filter
        if ($request->filled('submitted_date')) {
            $date = $request->submitted_date;
            $query->whereDate('submitted_at', $date);
        }
        
        $stories = $query->paginate(15);
        
        // Get counts for filter tabs
        $pendingCount = Story::where('status', 'pending')->count();
        $publishedCount = Story::where('status', 'published')->count();
        $draftCount = Story::where('status', 'draft')->count();
        $rejectedCount = Story::where('status', 'rejected')->count();
        
        // Get list of authors who have submitted stories
        $authors = \App\Models\User::whereIn('id', Story::select('user_id')->distinct())
            ->orderBy('name')
            ->get(['id', 'name', 'role']);
        
        return view('admin.pages.story-reviews.index', compact(
            'stories',
            'pendingCount',
            'publishedCount',
            'draftCount',
            'rejectedCount',
            'authors'
        ));
    }
    
    /**
     * Show the details of a story review request
     */
    public function show(Story $story)
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('home')->with('error', 'Bạn không có quyền truy cập trang này.');
        }
        
        // Load related data
        $story->load(['user', 'categories', 'chapters']);
        $chapterCount = $story->chapters()->count();
        
        return view('admin.pages.story-reviews.show', compact('story', 'chapterCount'));
    }
    
    /**
     * Approve a story
     */
    public function approve(Request $request, Story $story)
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('home')->with('error', 'Bạn không có quyền thực hiện hành động này.');
        }
        
        // Validate request
        $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);
        
        // Check if the story is pending
        if ($story->status !== 'pending') {
            return redirect()->back()->with('error', 'Truyện này không ở trạng thái chờ duyệt.');
        }
        
        try {
            // Update story status
            $story->update([
                'status' => 'published',
                'admin_note' => $request->admin_note,
                'reviewed_at' => now(),
            ]);
            
            // Notify the author
            if ($story->user) {
                $story->user->notify(new StoryStatusChanged($story));
            }
            
            return redirect()->route('admin.story-reviews.index')
                ->with('success', 'Truyện đã được phê duyệt và xuất bản thành công.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
    
    /**
     * Reject a story
     */
    public function reject(Request $request, Story $story)
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('home')->with('error', 'Bạn không có quyền thực hiện hành động này.');
        }
        
        // Validate request
        $request->validate([
            'admin_note' => 'required|string|max:1000',
        ], [
            'admin_note.required' => 'Vui lòng cung cấp lý do từ chối truyện.'
        ]);
        
        // Check if the story is pending
        if ($story->status !== 'pending') {
            return redirect()->back()->with('error', 'Truyện này không ở trạng thái chờ duyệt.');
        }
        
        try {
            // Update story status
            $story->update([
                'status' => 'rejected',
                'admin_note' => $request->admin_note,
                'reviewed_at' => now(),
            ]);
            
            // Notify the author
            if ($story->user) {
                $story->user->notify(new StoryStatusChanged($story));
            }
            
            return redirect()->route('admin.story-reviews.index')
                ->with('success', 'Truyện đã bị từ chối.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
} 