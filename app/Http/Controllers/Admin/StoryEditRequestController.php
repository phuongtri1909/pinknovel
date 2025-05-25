<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Story;
use App\Models\StoryEditRequest;
use App\Notifications\EditRequestStatusChanged;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class StoryEditRequestController extends Controller
{
    /**
     * Display a listing of story edit requests
     */
    public function index(Request $request)
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('home')->with('error', 'Bạn không có quyền truy cập trang này.');
        }
        
        $query = StoryEditRequest::with(['story', 'user'])
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
                  ->orWhereHas('story', function ($sq) use ($search) {
                      $sq->where('title', 'like', "%{$search}%");
                  })
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
        
        $editRequests = $query->paginate(15);
        
        // Get counts for filter tabs
        $pendingCount = StoryEditRequest::where('status', 'pending')->count();
        $approvedCount = StoryEditRequest::where('status', 'approved')->count();
        $rejectedCount = StoryEditRequest::where('status', 'rejected')->count();
        
        // Get list of authors who have submitted edit requests
        $authors = \App\Models\User::whereIn('id', StoryEditRequest::select('user_id')->distinct())
            ->orderBy('name')
            ->get(['id', 'name', 'role']);
        
        return view('admin.pages.edit-requests.index', compact(
            'editRequests',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'authors'
        ));
    }
    
    /**
     * Show the details of an edit request
     */
    public function show(StoryEditRequest $editRequest)
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('home')->with('error', 'Bạn không có quyền truy cập trang này.');
        }
        
        $story = $editRequest->story;
        $categoryIds = collect(json_decode($editRequest->categories_data, true))->pluck('id')->toArray();
        
        return view('admin.pages.edit-requests.show', compact('editRequest', 'story', 'categoryIds'));
    }
    
    /**
     * Approve an edit request
     */
    public function approve(Request $request, StoryEditRequest $editRequest)
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('home')->with('error', 'Bạn không có quyền thực hiện hành động này.');
        }
        
        // Validate request
        $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);
        
        // Check if the edit request is pending
        if ($editRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'Yêu cầu chỉnh sửa này đã được xử lý.');
        }
        
        $story = $editRequest->story;
        
        // Begin transaction
        DB::beginTransaction();
        try {
            // Store old cover paths if we're updating them
            $oldCovers = null;
            if ($editRequest->cover) {
                $oldCovers = [
                    $story->cover,
                    $story->cover_medium,
                    $story->cover_thumbnail
                ];
            }
            
            // Update story with edit request data
            $story->update([
                'title' => $editRequest->title,
                'slug' => $editRequest->slug,
                'description' => $editRequest->description,
                'author_name' => $editRequest->author_name,
                'story_type' => $editRequest->story_type,
                'cover' => $editRequest->cover ?? $story->cover,
                'cover_medium' => $editRequest->cover_medium ?? $story->cover_medium,
                'cover_thumbnail' => $editRequest->cover_thumbnail ?? $story->cover_thumbnail,
                'is_monopoly' => $editRequest->is_monopoly ?? $story->is_monopoly,
                'translator_name' => $editRequest->translator_name ?? $story->translator_name,
                'is_18_plus' => $editRequest->is_18_plus ?? $story->is_18_plus,
            ]);
            
            // Update categories
            if ($editRequest->categories_data) {
                $categoryIds = collect(json_decode($editRequest->categories_data, true))->pluck('id')->toArray();
                $story->categories()->sync($categoryIds);
            }
            
            // Update edit request status
            $editRequest->update([
                'status' => 'approved',
                'admin_note' => $request->admin_note,
                'reviewed_at' => now(),
            ]);
            
            DB::commit();
            
            // Delete old covers if they were replaced
            if ($oldCovers && $editRequest->cover) {
                Storage::disk('public')->delete($oldCovers);
            }
            
            // Send notification to the author
            $editRequest->user->notify(new EditRequestStatusChanged($editRequest));
            
            return redirect()->route('admin.edit-requests.index')
                ->with('success', 'Yêu cầu chỉnh sửa đã được phê duyệt và áp dụng thành công.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
    
    /**
     * Reject an edit request
     */
    public function reject(Request $request, StoryEditRequest $editRequest)
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('home')->with('error', 'Bạn không có quyền thực hiện hành động này.');
        }
        
        // Validate request
        $request->validate([
            'admin_note' => 'required|string|max:1000',
        ], [
            'admin_note.required' => 'Vui lòng cung cấp lý do từ chối yêu cầu chỉnh sửa.'
        ]);
        
        // Check if the edit request is pending
        if ($editRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'Yêu cầu chỉnh sửa này đã được xử lý.');
        }
        
        DB::beginTransaction();
        try {
            // Update edit request status
            $editRequest->update([
                'status' => 'rejected',
                'admin_note' => $request->admin_note,
                'reviewed_at' => now(),
            ]);
            
            // If there were new cover images, delete them
            if ($editRequest->cover) {
                Storage::disk('public')->delete([
                    $editRequest->cover,
                    $editRequest->cover_medium,
                    $editRequest->cover_thumbnail
                ]);
            }
            
            DB::commit();
            
            // Send notification to the author
            $editRequest->user->notify(new EditRequestStatusChanged($editRequest));
            
            return redirect()->route('admin.edit-requests.index')
                ->with('success', 'Yêu cầu chỉnh sửa đã được từ chối.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}
