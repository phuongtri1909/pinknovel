<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\StoryTransferHistory;

class StoryTransferController extends Controller
{
    /**
     * Display story transfer management page
     */
    public function index(Request $request)
    {
        // Only admin can access
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }

        $query = Story::with(['user', 'categories'])
            ->withCount(['chapters', 'bookmarks', 'comments']);

        // Search by story title or author
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('author_name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by current author
        if ($request->filled('current_author')) {
            $query->where('user_id', $request->current_author);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by story type
        if ($request->filled('story_type')) {
            $query->where('story_type', $request->story_type);
        }

        $stories = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get all authors for filters and transfer
        $authors = User::where('role', 'author')
            ->orWhere('role', 'admin')
            ->orderBy('name')
            ->get();

        return view('admin.pages.story.transfer.index', compact('stories', 'authors'));
    }

    /**
     * Show transfer form for specific story
     */
    public function show(Story $story)
    {
        // Only admin can access
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }

        // Load story with relationships
        $story->load(['user', 'categories', 'chapters']);

        // Get all authors except current owner
        $authors = User::where('role', 'author')
                  ->orWhere('role', 'admin')
                  ->where('id', '!=', $story->user_id)
                  ->orderBy('name')
                  ->get();

        // Calculate revenue (NEW)
        $storyRevenue = $story->storyPurchases()->sum('amount_paid');
        $chapterRevenue = $story->chapterPurchases()->sum('amount_paid');
        $totalRevenue = $storyRevenue + $chapterRevenue;

        // Get story statistics with proper calculations (UPDATED)
        $storyStats = [
            'total_chapters' => $story->chapters()->count(),
            'published_chapters' => $story->chapters()->where('status', 'published')->count(),
            'total_views' => $story->total_views ?? 0,
            'total_bookmarks' => $story->bookmarks()->count(),
            'total_comments' => $story->comments()->count(),
            'story_purchases' => $story->storyPurchases()->count(),
            'chapter_purchases' => $story->chapterPurchases()->count(),
            'story_revenue' => $storyRevenue, // NEW
            'chapter_revenue' => $chapterRevenue, // NEW
            'total_revenue' => $totalRevenue, // UPDATED
        ];

        return view('admin.pages.story.transfer.show', compact('story', 'authors', 'storyStats'));
    }

    /**
     * Transfer story to new author
     */
    public function transfer(Request $request, Story $story)
    {
        // Only admin can transfer
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện chức năng này.'
            ], 403);
        }

        $request->validate([
            'new_author_id' => 'required|exists:users,id',
            'reason' => 'required|string|max:500',
        ], [
            'new_author_id.required' => 'Vui lòng chọn tác giả mới',
            'new_author_id.exists' => 'Tác giả được chọn không tồn tại',
            'reason.required' => 'Vui lòng nhập lý do chuyển nhượng',
            'reason.max' => 'Lý do không được vượt quá 500 ký tự',
        ]);

        // Check if new author has author role
        $newAuthor = User::findOrFail($request->new_author_id);
        if (!in_array($newAuthor->role, ['author', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Người dùng được chọn phải có vai trò tác giả hoặc admin.'
            ], 400);
        }

        // Check if trying to transfer to same author
        if ($story->user_id == $request->new_author_id) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể chuyển nhượng cho chính tác giả hiện tại.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $oldAuthor = $story->user;
            $oldAuthorId = $story->user_id;

            // Calculate revenue at transfer time
            $storyRevenue = $story->storyPurchases()->sum('amount_paid');
            $chapterRevenue = $story->chapterPurchases()->sum('amount_paid');
            $totalRevenue = $storyRevenue + $chapterRevenue;

            // Get story metadata before transfer (UPDATED)
            $transferMetadata = [
                'chapters_count' => $story->chapters()->count(),
                'published_chapters' => $story->chapters()->where('status', 'published')->count(),
                'total_views' => $story->total_views ?? 0,
                'bookmarks_count' => $story->bookmarks()->count(),
                'comments_count' => $story->comments()->count(),
                'story_status' => $story->status,
                'story_type' => $story->story_type,
                'is_18_plus' => $story->is_18_plus,
                
                // Revenue data - NEW
                'revenue' => [
                    'story_purchases' => [
                        'count' => $story->storyPurchases()->count(),
                        'total_amount' => $storyRevenue
                    ],
                    'chapter_purchases' => [
                        'count' => $story->chapterPurchases()->count(),
                        'total_amount' => $chapterRevenue
                    ],
                    'total_revenue' => $totalRevenue
                ],
                
                // Additional metrics - NEW
                'metrics' => [
                    'bookmark_rate' => $story->total_views > 0 ? 
                        round(($story->bookmarks()->count() / $story->total_views) * 100, 2) : 0,
                    'comment_rate' => $story->total_views > 0 ? 
                        round(($story->comments()->count() / $story->total_views) * 100, 2) : 0,
                    'purchase_rate' => $story->total_views > 0 ? 
                        round((($story->storyPurchases()->count() + $story->chapterPurchases()->count()) / $story->total_views) * 100, 2) : 0,
                ],
                
                // Transfer timestamp
                'captured_at' => now()->toISOString(),
            ];

            // Update story ownership
            $story->update([
                'user_id' => $newAuthor->id,
                'admin_note' => ($story->admin_note ? $story->admin_note . "\n\n" : '') .
                    "[" . now()->format('d/m/Y H:i') . "] Chuyển nhượng từ {$oldAuthor->name} ({$oldAuthor->email}) sang {$newAuthor->name} ({$newAuthor->email}). Lý do: {$request->reason}. Doanh thu tại thời điểm chuyển: " . number_format($totalRevenue) . " xu. Thực hiện bởi: " . Auth::user()->name
            ]);

            // Update all chapters ownership
            $story->chapters()->update(['user_id' => $newAuthor->id]);

            // Create transfer history record
            StoryTransferHistory::createRecord([
                'story_id' => $story->id,
                'story_title' => $story->title,
                'story_slug' => $story->slug,
                'old_author_id' => $oldAuthorId,
                'old_author_name' => $oldAuthor->name,
                'old_author_email' => $oldAuthor->email,
                'new_author_id' => $newAuthor->id,
                'new_author_name' => $newAuthor->name,
                'new_author_email' => $newAuthor->email,
                'reason' => $request->reason,
                'transfer_type' => StoryTransferHistory::TYPE_SINGLE,
                'transfer_metadata' => $transferMetadata, // Updated with revenue data
                'transferred_by' => Auth::id(),
                'transferred_by_name' => Auth::user()->name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'transferred_at' => now(),
                'status' => StoryTransferHistory::STATUS_COMPLETED,
            ]);

            // Optional: Send notifications
            if ($request->notify_old_author) {
                // TODO: Send notification to old author
                // $this->sendTransferNotification($oldAuthor, $story, $newAuthor, 'transferred_from');
            }

            if ($request->notify_new_author) {
                // TODO: Send notification to new author
                // $this->sendTransferNotification($newAuthor, $story, $oldAuthor, 'transferred_to');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Đã chuyển nhượng truyện '{$story->title}' từ {$oldAuthor->name} sang {$newAuthor->name} thành công. Tổng doanh thu: " . number_format($totalRevenue) . " xu.",
                'redirect_url' => route('admin.story-transfer.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            // Create failed transfer history record
            try {
                StoryTransferHistory::createRecord([
                    'story_id' => $story->id,
                    'story_title' => $story->title,
                    'story_slug' => $story->slug,
                    'old_author_id' => $story->user_id,
                    'old_author_name' => $story->user->name,
                    'old_author_email' => $story->user->email,
                    'new_author_id' => $request->new_author_id,
                    'new_author_name' => $newAuthor->name,
                    'new_author_email' => $newAuthor->email,
                    'reason' => $request->reason,
                    'transfer_type' => StoryTransferHistory::TYPE_SINGLE,
                    'transferred_by' => Auth::id(),
                    'transferred_by_name' => Auth::user()->name,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'transferred_at' => now(),
                    'status' => StoryTransferHistory::STATUS_FAILED,
                    'notes' => 'Error: ' . $e->getMessage(),
                ]);
            } catch (\Exception $historyException) {
                Log::error('Failed to create transfer history', [
                    'original_error' => $e->getMessage(),
                    'history_error' => $historyException->getMessage()
                ]);
            }

            Log::error('Story Transfer Error', [
                'story_id' => $story->id,
                'old_author_id' => $story->user_id,
                'new_author_id' => $request->new_author_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi chuyển nhượng truyện: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get author stories for AJAX
     */
    public function getAuthorStories(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $authorId = $request->author_id;

        $stories = Story::where('user_id', $authorId)
            ->select('id', 'title', 'status', 'created_at')
            ->withCount(['chapters', 'bookmarks'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($stories);
    }

    /**
     * Bulk transfer stories
     */
    public function bulkTransfer(Request $request)
    {
        // Only admin can bulk transfer
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện chức năng này.'
            ], 403);
        }

        $request->validate([
            'story_ids' => 'required|array|min:1',
            'story_ids.*' => 'exists:stories,id',
            'new_author_id' => 'required|exists:users,id',
            'reason' => 'required|string|max:500',
        ], [
            'story_ids.required' => 'Vui lòng chọn ít nhất một truyện',
            'story_ids.min' => 'Vui lòng chọn ít nhất một truyện',
            'new_author_id.required' => 'Vui lòng chọn tác giả mới',
            'reason.required' => 'Vui lòng nhập lý do chuyển nhượng',
        ]);

        // Check if new author has author role
        $newAuthor = User::findOrFail($request->new_author_id);
        if (!in_array($newAuthor->role, ['author', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Người dùng được chọn phải có vai trò tác giả hoặc admin.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $transferredStories = [];
            $errors = [];
            $totalBatchRevenue = 0;

            foreach ($request->story_ids as $storyId) {
                $story = Story::findOrFail($storyId);

                // Skip if already owned by new author
                if ($story->user_id == $request->new_author_id) {
                    $errors[] = "Truyện '{$story->title}' đã thuộc về tác giả này";
                    continue;
                }

                $oldAuthor = $story->user;

                // Calculate revenue for this story
                $storyRevenue = $story->storyPurchases()->sum('amount_paid');
                $chapterRevenue = $story->chapterPurchases()->sum('amount_paid');
                $totalRevenue = $storyRevenue + $chapterRevenue;
                $totalBatchRevenue += $totalRevenue;

                // Get story metadata (UPDATED)
                $transferMetadata = [
                    'chapters_count' => $story->chapters()->count(),
                    'published_chapters' => $story->chapters()->where('status', 'published')->count(),
                    'total_views' => $story->total_views ?? 0,
                    'bookmarks_count' => $story->bookmarks()->count(),
                    'bulk_transfer_batch' => true,
                    'batch_size' => count($request->story_ids),
                    
                    // Revenue data for individual story - NEW
                    'revenue' => [
                        'story_purchases_count' => $story->storyPurchases()->count(),
                        'story_revenue' => $storyRevenue,
                        'chapter_purchases_count' => $story->chapterPurchases()->count(),
                        'chapter_revenue' => $chapterRevenue,
                        'total_revenue' => $totalRevenue,
                    ],
                    
                    'captured_at' => now()->toISOString(),
                ];

                // Update story
                $story->update([
                    'user_id' => $newAuthor->id,
                    'admin_note' => ($story->admin_note ? $story->admin_note . "\n\n" : '') .
                        "[" . now()->format('d/m/Y H:i') . "] Bulk chuyển nhượng từ {$oldAuthor->name} sang {$newAuthor->name}. Lý do: {$request->reason}. Doanh thu: " . number_format($totalRevenue) . " xu. Thực hiện bởi: " . Auth::user()->name
                ]);

                // Update chapters
                $story->chapters()->update(['user_id' => $newAuthor->id]);

                // Create transfer history record
                StoryTransferHistory::createRecord([
                    'story_id' => $story->id,
                    'story_title' => $story->title,
                    'story_slug' => $story->slug,
                    'old_author_id' => $oldAuthor->id, // Fixed bug
                    'old_author_name' => $oldAuthor->name,
                    'old_author_email' => $oldAuthor->email,
                    'new_author_id' => $newAuthor->id,
                    'new_author_name' => $newAuthor->name,
                    'new_author_email' => $newAuthor->email,
                    'reason' => $request->reason,
                    'transfer_type' => StoryTransferHistory::TYPE_BULK,
                    'transfer_metadata' => $transferMetadata, // Updated with revenue data
                    'transferred_by' => Auth::id(),
                    'transferred_by_name' => Auth::user()->name,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'transferred_at' => now(),
                    'status' => StoryTransferHistory::STATUS_COMPLETED,
                ]);

                $transferredStories[] = $story->title;
            }

            DB::commit();

            $message = "Đã chuyển nhượng " . count($transferredStories) . " truyện sang {$newAuthor->name} thành công. Tổng doanh thu: " . number_format($totalBatchRevenue) . " xu.";
            if (!empty($errors)) {
                $message .= " Lỗi: " . implode(', ', $errors);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'transferred_count' => count($transferredStories),
                'total_revenue' => $totalBatchRevenue,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            // ...existing error handling...
        }
    }

    /**
     * View transfer history
     */
    public function history(Request $request)
    {
        // Only admin can access
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }

        $query = StoryTransferHistory::with(['story', 'oldAuthor', 'newAuthor', 'transferredBy']);

        // Filter by story
        if ($request->filled('story_id')) {
            $query->where('story_id', $request->story_id);
        }

        // Filter by old author
        if ($request->filled('old_author_id')) {
            $query->where('old_author_id', $request->old_author_id);
        }

        // Filter by new author
        if ($request->filled('new_author_id')) {
            $query->where('new_author_id', $request->new_author_id);
        }

        // Filter by transfer type
        if ($request->filled('transfer_type')) {
            $query->where('transfer_type', $request->transfer_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('transferred_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('transferred_at', '<=', $request->date_to . ' 23:59:59');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('story_title', 'like', "%{$search}%")
                  ->orWhere('old_author_name', 'like', "%{$search}%")
                  ->orWhere('new_author_name', 'like', "%{$search}%")
                  ->orWhere('transferred_by_name', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        $histories = $query->orderBy('transferred_at', 'desc')->paginate(20);

        // Get data for filters
        $authors = User::where('role', 'author')
                  ->orWhere('role', 'admin')
                  ->orderBy('name')
                  ->get();

        return view('admin.pages.story.transfer.history', compact('histories', 'authors'));
    }

    /**
     * Show single transfer history details
     */
    public function historyShow(StoryTransferHistory $history)
    {
        // Only admin can access
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }

        $history->load(['story', 'oldAuthor', 'newAuthor', 'transferredBy']);

        return view('admin.pages.story.transfer.history_show', compact('history'));
    }
}
