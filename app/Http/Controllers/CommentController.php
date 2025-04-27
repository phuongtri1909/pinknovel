<?php

namespace App\Http\Controllers;

use App\Models\Story;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Models\CommentReaction;

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

        if (!auth()->user()->isAdmin() || $comment->level !== 0) {
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
            ->whereNull('reply_id')
            ->where('is_pinned', true)
            ->latest()
            ->get();

        $regularComments = Comment::with(['user', 'replies.user', 'reactions'])
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
        $authUser = auth()->user();
        $comment = Comment::with('user')->find($comment);

        if (!$comment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy bình luận này' . $comment
            ], 404);
        }

        // Admin can delete all comments
        if ($authUser->role === 'admin') {
            $comment->delete();
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
            return response()->json([
                'status' => 'success',
                'message' => 'Xóa bình luận thành công'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Không có quyền thực hiện'
        ], 403);
    }

    public function storeClient(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vui lòng đăng nhập để bình luận'
            ], 401);
        }

        $request->validate([
            'comment' => 'required|max:700',
            'story_id' => 'required|exists:stories,id',
            'reply_id' => 'nullable|exists:comments,id'
        ]);

        $story = Story::findOrFail($request->story_id);

        $user = auth()->user();
        if ($user->ban_comment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tài khoản của bạn đã bị cấm bình luận'
            ], 403);
        }

        $parentComment = null;
        if ($request->reply_id) {
            $parentComment = Comment::where('story_id', $request->story_id)
                ->find($request->reply_id);

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
        }

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'story_id' => $request->story_id,
            'comment' => $request->comment,
            'reply_id' => $request->reply_id,
            'level' => $request->reply_id ? ($parentComment->level + 1) : 0,
        ]);

        $comment->load('user');

        return response()->json([
            'status' => 'success',
            'message' => 'Đã thêm bình luận',
            'html' => view('components.comments-item', compact('comment'))->render()
        ]);
    }

   

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Story $story)
    {
        $search = $request->search;
        $userId = $request->user;
        $authUser = auth()->user();

        $query = $story->comments()->with(['user', 'replies']);

        // If mod, only show user and vip comments
        if ($authUser->role === 'mod') {
            $query->whereHas('user', function ($q) {
                $q->whereIn('role', ['user']);
            });
        }

        if ($search) {
            $query->where('comment', 'like', '%' . $search . '%');
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $comments = $query->orderBy('id', 'desc')->paginate(15);

        $usersQuery = \App\Models\User::whereHas('comments')
            ->where('active', 'active');

        if ($authUser->role === 'mod') {
            $usersQuery->whereIn('role', ['user', 'vip']);
        }

        $users = $usersQuery->orderBy('name')->get();

        $totalComments = Comment::count();

        return view('admin.pages.comments.index', compact('comments', 'users', 'totalComments', 'story'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($comment)
    {
        $authUser = auth()->user();
        $comment = Comment::find($comment);
        if (!$comment) {
            return redirect()->route('comments.index')->with('error', 'Không tìm thấy bình luận này');
        }

        if (
            $authUser->role === 'admin' ||
            ($authUser->role === 'mod' && (!$comment->user || $comment->user->role !== 'admin'))
        ) {
            $comment->delete();
            return redirect()->route('comments.index')->with('success', 'Xóa bình luận thành công');
        }

        return redirect()->route('comments.index')->with('error', 'Không thể xóa bình luận của Admin');
    }
}
