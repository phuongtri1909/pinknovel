<?php

namespace App\Http\Controllers;

use App\Models\Story;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Models\CommentReaction;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{

    public function react(Request $request, $commentId)
    {
        if (!auth()->check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vui lòng đăng nhập để thực hiện',
                'redirect' => route('login')
            ], 401);
        }

        $comment = Comment::findOrFail($commentId);
        $type = $request->type;
        $userId = auth()->id();

        // Validate reaction type
        if (!in_array($type, ['like', 'dislike'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Loại phản ứng không hợp lệ'
            ], 400);
        }

        // Check if user already reacted to this comment
        $existingReaction = CommentReaction::where('comment_id', $commentId)
            ->where('user_id', $userId)
            ->first();

        if ($existingReaction) {
            if ($existingReaction->type === $type) {
                // If same reaction type, remove it (toggle off)
                $existingReaction->delete();
                $message = $type === 'like' ? 'Đã bỏ thích bình luận' : 'Đã bỏ không thích bình luận';
            } else {
                // If different reaction type, update it
                $existingReaction->update(['type' => $type]);
                $message = $type === 'like' ? 'Đã thích bình luận' : 'Đã không thích bình luận';
            }
        } else {
            // Create new reaction
            CommentReaction::create([
                'comment_id' => $commentId,
                'user_id' => $userId,
                'type' => $type
            ]);
            $message = $type === 'like' ? 'Đã thích bình luận' : 'Đã không thích bình luận';
        }

        // Get updated counts
        $likes = CommentReaction::where('comment_id', $commentId)->where('type', 'like')->count();
        $dislikes = CommentReaction::where('comment_id', $commentId)->where('type', 'dislike')->count();

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'likes' => $likes,
            'dislikes' => $dislikes
        ]);
    }


    public function loadComments(Request $request, $storyId)
    {
        $pinnedComments = Comment::with(['user', 'replies.user', 'reactions'])
            ->where('story_id', $storyId)
            ->whereNull('reply_id')
            ->where('is_pinned', true)
            ->latest('pinned_at')
            ->get();

        $regularComments = Comment::with(['user', 'replies.user', 'reactions'])
            ->where('story_id', $storyId)
            ->whereNull('reply_id')
            ->where('is_pinned', false)
            ->latest()
            ->paginate(10);

        if ($request->ajax()) {
            $html = view('components.comments-list', compact('pinnedComments', 'regularComments'))->render();
            return response()->json([
                'html' => $html,
                'hasMore' => $regularComments->hasMorePages()
            ]);
        }

        return view('components.comment', compact('pinnedComments', 'regularComments', 'storyId'));
    }

    public function togglePin($commentId)
    {
        $comment = Comment::findOrFail($commentId);

        if (auth()->user()->role !== 'admin' || $comment->level !== 0) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        if (!$comment->is_pinned) {
            $pinnedCount = Comment::where('is_pinned', true)->count();
            if ($pinnedCount >= 3) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Đã đạt giới hạn số bình luận được ghim'
                ], 400);
            }
        }

        $comment->is_pinned = !$comment->is_pinned;
        $comment->pinned_at = $comment->is_pinned ? now() : null;
        $comment->save();

        // Get updated comments
        $pinnedComments = Comment::with(['user', 'replies.user', 'reactions'])
            ->where('story_id', $comment->story_id)
            ->whereNull('reply_id')
            ->where('is_pinned', true)
            ->latest('pinned_at')
            ->get();

        $regularComments = Comment::with(['user', 'replies.user', 'reactions'])
            ->where('story_id', $comment->story_id)
            ->whereNull('reply_id')
            ->where('is_pinned', false)
            ->latest()
            ->paginate(10);

        $html = view('components.comments-list', compact('pinnedComments', 'regularComments'))->render();

        return response()->json([
            'status' => 'success',
            'message' => $comment->is_pinned ? 'Đã ghim bình luận' : 'Đã bỏ ghim bình luận',
            'is_pinned' => $comment->is_pinned,
            'html' => $html
        ]);
    }

    public function deleteComment($comment)
    {
        try {
            $authUser = auth()->user();
            $comment = Comment::with('user')->find($comment);

            if (!$comment) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Bình luận đã được xóa'
                ]);
            }

            // Check if this is a pinned comment
            $isPinned = $comment->is_pinned;
            $storyId = $comment->story_id;

            // Admin can delete all comments
            if ($authUser->role === 'admin') {
                $comment->delete();
                
                // If it was a pinned comment, return updated comments list
                if ($isPinned) {
                    $pinnedComments = Comment::with(['user', 'replies.user', 'reactions'])
                        ->where('story_id', $storyId)
                        ->whereNull('reply_id')
                        ->where('is_pinned', true)
                        ->latest('pinned_at')
                        ->get();
                    
                    $regularComments = Comment::with(['user', 'replies.user', 'reactions'])
                        ->where('story_id', $storyId)
                        ->whereNull('reply_id')
                        ->where('is_pinned', false)
                        ->latest()
                        ->paginate(10);
                    
                    $html = view('components.comments-list', compact('pinnedComments', 'regularComments'))->render();
                    
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Xóa bình luận thành công',
                        'isPinned' => true,
                        'html' => $html
                    ]);
                }
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Xóa bình luận thành công'
                ]);
            }

            // Mod can delete except admin comments
            if ($authUser->role === 'mod') {
                if ($comment->user && $comment->user->role === 'admin') {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Không thể xóa bình luận của Admin'
                    ], 403);
                }
                $comment->delete();
                
                // If it was a pinned comment, return updated comments list
                if ($isPinned) {
                    $pinnedComments = Comment::with(['user', 'replies.user', 'reactions'])
                        ->where('story_id', $storyId)
                        ->whereNull('reply_id')
                        ->where('is_pinned', true)
                        ->latest('pinned_at')
                        ->get();
                    
                    $regularComments = Comment::with(['user', 'replies.user', 'reactions'])
                        ->where('story_id', $storyId)
                        ->whereNull('reply_id')
                        ->where('is_pinned', false)
                        ->latest()
                        ->paginate(10);
                    
                    $html = view('components.comments-list', compact('pinnedComments', 'regularComments'))->render();
                    
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Xóa bình luận thành công',
                        'isPinned' => true,
                        'html' => $html
                    ]);
                }
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Xóa bình luận thành công'
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Không có quyền thực hiện'
            ], 403);
        } catch (\Exception $e) {
           Log::error('Error deleting comment: ' . $e->getMessage());
            $stillExists = Comment::find($comment);
            if (!$stillExists) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Bình luận đã được xóa'
                ]);
            }
            
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi xóa bình luận'
            ], 500);
        }
    }

    public function storeClient(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vui lòng đăng nhập để bình luận'
            ], 401);
        }

        // Validate the request
        $validated = $request->validate([
            'comment' => 'required|max:700',
            'story_id' => 'required|exists:stories,id',
            'reply_id' => 'nullable|exists:comments,id'
        ]);

        $user = auth()->user();
        
        // Check if user is banned from commenting
        if ($user->ban_comment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tài khoản của bạn đã bị cấm bình luận'
            ], 403);
        }

        // Find the story
        $story = Story::findOrFail($validated['story_id']);

        $parentComment = null;
        $level = 0;
        
        // Check reply logic
        if (!empty($validated['reply_id'])) {
            $parentComment = Comment::where('story_id', $validated['story_id'])
                ->find($validated['reply_id']);

            if (!$parentComment) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy bình luận để trả lời'
                ], 404);
            }

            if ($parentComment->level >= 2) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không thể trả lời bình luận này'
                ], 403);
            }
            
            $level = $parentComment->level + 1;
        }
        
        // Check for potential duplicate comments within last 30 seconds
        $recentComments = Comment::where('user_id', $user->id)
            ->where('story_id', $validated['story_id'])
            ->where('comment', $validated['comment'])
            ->where('created_at', '>=', now()->subSeconds(30))
            ->exists();
        
        if ($recentComments) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bình luận của bạn đã được gửi, vui lòng không gửi lại.'
            ], 400);
        }

        try {
            // Create the comment inside a transaction
            $comment = Comment::create([
                'user_id' => $user->id,
                'story_id' => $validated['story_id'],
                'comment' => $validated['comment'],
                'reply_id' => $validated['reply_id'] ?? null,
                'level' => $level,
            ]);

            // Load relations for the view
            $comment->load(['user', 'reactions']);

            // Complete daily task for commenting
            \App\Models\UserDailyTask::completeTask(
                $user->id,
                \App\Models\DailyTask::TYPE_COMMENT,
                [
                    'story_id' => $validated['story_id'],
                    'comment_id' => $comment->id,
                    'comment_time' => now()->toISOString(),
                ],
                $request
            );

            // Get pinned comments for proper rendering
            $pinnedComments = Comment::with(['user', 'replies.user', 'reactions'])
                ->where('story_id', $validated['story_id'])
                ->whereNull('reply_id')
                ->where('is_pinned', true)
                ->latest('pinned_at')
                ->get();

            if (empty($validated['reply_id'])) {
                // Only return the single comment HTML if it's a reply
                return response()->json([
                    'status' => 'success',
                    'message' => 'Đã thêm bình luận',
                    'html' => view('components.comments-item', compact('comment'))->render(),
                    'isPinned' => false,
                    'pinnedComments' => $pinnedComments->count() > 0 ? view('components.comments-list', ['pinnedComments' => $pinnedComments])->render() : null
                ]);
            } else {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Đã thêm bình luận',
                    'html' => view('components.comments-item', compact('comment'))->render()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error saving comment: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi khi lưu bình luận'
            ], 500);
        }
    }



    // Add a new method to view all comments
    public function allComments(Request $request)
    {
        $authUser = auth()->user();
        $search = $request->search;
        $userId = $request->user;
        $storyId = $request->story;
        $date = $request->date;

        // Begin with all comments query
        $query = Comment::with(['user', 'story']);

        // Apply role-based restrictions
        if ($authUser->role === 'mod') {
            $query->whereHas('user', function ($q) {
                $q->whereIn('role', ['user']);
            });
        }

        // Create a base query that we'll modify based on filters
        $baseQuery = clone $query;

        // Find all parent IDs of comments that match our search (for showing full threads)
        $matchingChildIds = collect([]);
        $parentIdsToInclude = collect([]);

        // If we're searching content, user, or date, we need to find matches in child comments too
        if ($search || $userId || $date) {
            $childQuery = clone $baseQuery;

            // Apply content search to child query
            if ($search) {
                $childQuery->where('comment', 'like', '%' . $search . '%');
            }

            // Apply user filter to child query
            if ($userId) {
                $childQuery->where('user_id', $userId);
            }

            // Apply date filter to child query
            if ($date) {
                $childQuery->whereDate('created_at', $date);
            }

            // Get the IDs of all comments matching our filters
            $matchingChildIds = $childQuery->pluck('id');

            // Find all parent IDs for these matching comments
            if ($matchingChildIds->isNotEmpty()) {
                $parentIds = Comment::whereIn('id', $matchingChildIds)
                    ->whereNotNull('reply_id')
                    ->pluck('reply_id');

                // Get all grandparent IDs recursively
                $allParentIds = collect([]);
                $currentParentIds = $parentIds;

                while ($currentParentIds->isNotEmpty()) {
                    $allParentIds = $allParentIds->merge($currentParentIds);
                    $currentParentIds = Comment::whereIn('id', $currentParentIds)
                        ->whereNotNull('reply_id')
                        ->pluck('reply_id');
                }

                $parentIdsToInclude = $allParentIds->unique();
            }
        }

        // Apply story filter (this applies to all comments regardless of level)
        if ($storyId) {
            $query->where('story_id', $storyId);
        }

        // Now build our main query
        // Get top-level comments that either:
        // 1. Match our filters directly, or
        // 2. Have child comments that match our filters
        $finalQuery = Comment::with(['user', 'story'])
            ->with(['replies.user', 'replies.replies.user', 'replies.replies.replies.user'])
            ->whereNull('reply_id');

        // Apply direct filters
        if ($search) {
            $finalQuery->where(function ($q) use ($search, $parentIdsToInclude, $matchingChildIds) {
                $q->where('comment', 'like', '%' . $search . '%')
                    ->orWhereIn('id', $parentIdsToInclude)
                    ->orWhereIn('id', $matchingChildIds->filter(function ($id) {
                        return Comment::find($id) && Comment::find($id)->reply_id === null;
                    }));
            });
        }

        if ($userId) {
            $finalQuery->where(function ($q) use ($userId, $parentIdsToInclude, $matchingChildIds) {
                $q->where('user_id', $userId)
                    ->orWhereIn('id', $parentIdsToInclude)
                    ->orWhereIn('id', $matchingChildIds->filter(function ($id) {
                        return Comment::find($id) && Comment::find($id)->reply_id === null;
                    }));
            });
        }

        if ($date) {
            $finalQuery->where(function ($q) use ($date, $parentIdsToInclude, $matchingChildIds) {
                $q->whereDate('created_at', $date)
                    ->orWhereIn('id', $parentIdsToInclude)
                    ->orWhereIn('id', $matchingChildIds->filter(function ($id) {
                        return Comment::find($id) && Comment::find($id)->reply_id === null;
                    }));
            });
        }

        // Apply story filter
        if ($storyId) {
            $finalQuery->where('story_id', $storyId);
        }

        // Apply role-based restrictions
        if ($authUser->role === 'mod') {
            $finalQuery->whereHas('user', function ($q) {
                $q->whereIn('role', ['user']);
            });
        }

        $comments = $finalQuery->orderBy('id', 'desc')->paginate(15);

        // Get all stories for the filter dropdown
        $stories = Story::orderBy('title')->get();

        // Get all users who have commented
        $usersQuery = \App\Models\User::whereHas('comments')
            ->where('active', 'active');

        if ($authUser->role === 'mod') {
            $usersQuery->whereIn('role', ['user', 'vip']);
        }

        $users = $usersQuery->orderBy('name')->get();

        $totalComments = Comment::count();

        return view('admin.pages.comments.all', compact('comments', 'users', 'stories', 'totalComments'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($comment)
    {
        $authUser = auth()->user();
        $comment = Comment::find($comment);
        if (!$comment) {
            return redirect()->route('comments.all')->with('error', 'Không tìm thấy bình luận này');
        }

        if (
            $authUser->role === 'admin' ||
            ($authUser->role === 'mod' && (!$comment->user || $comment->user->role !== 'admin'))
        ) {
            $comment->delete();
            return redirect()->route('comments.all')->with('success', 'Xóa bình luận thành công');
        }

        return redirect()->route('comments.all')->with('error', 'Không thể xóa bình luận của Admin');
    }
}
