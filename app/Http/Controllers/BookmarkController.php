<?php

namespace App\Http\Controllers;

use App\Models\Story;
use App\Models\Bookmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Toggle bookmark for a story
     */
    public function toggle(Request $request)
    {
        // Validate request
        $request->validate([
            'story_id' => 'required|exists:stories,id',
            'chapter_id' => 'nullable|exists:chapters,id',
        ]);

        $userId = Auth::id();
        $storyId = $request->story_id;
        $chapterId = $request->chapter_id;
        
        // Thực hiện toggle bookmark với chương hiện tại
        $result = Bookmark::toggleBookmark($userId, $storyId, $chapterId);
        
        return response()->json($result);
    }
    
    /**
     * Check if a story is bookmarked
     */
    public function checkStatus(Request $request)
    {
        // Validate request
        $request->validate([
            'story_id' => 'required|exists:stories,id',
        ]);
        
        $userId = Auth::id();
        $storyId = $request->story_id;
        
        // Kiểm tra trạng thái bookmark
        $isBookmarked = Bookmark::isBookmarked($userId, $storyId);
        
        return response()->json([
            'is_bookmarked' => $isBookmarked
        ]);
    }
    
    /**
     * Get user's bookmarks
     */
    public function getUserBookmarks()
    {
        $userId = Auth::id();
        $bookmarks = Bookmark::with(['story', 'lastChapter'])
                            ->where('user_id', $userId)
                            ->orderBy('last_read_at', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->get();
        
        return view('pages.information.bookmarks', compact('bookmarks'));
    }
    
    /**
     * Update current chapter for a bookmarked story
     */
    public function updateCurrentChapter(Request $request)
    {
        $request->validate([
            'story_id' => 'required|exists:stories,id',
            'chapter_id' => 'required|exists:chapters,id',
        ]);
        
        $userId = Auth::id();
        $storyId = $request->story_id;
        $chapterId = $request->chapter_id;
        
        // Kiểm tra xem đã bookmark chưa và cập nhật
        $bookmark = Bookmark::where('user_id', $userId)
                           ->where('story_id', $storyId)
                           ->first();
                           
        if ($bookmark) {
            $updated = Bookmark::saveCurrentChapter($userId, $storyId, $chapterId);
            return response()->json([
                'success' => $updated,
                'message' => $updated ? 'Đã cập nhật vị trí đọc' : 'Không thể cập nhật vị trí đọc'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Bạn chưa đánh dấu truyện này'
        ]);
    }

    /**
     * Remove bookmark
     */
    public function remove(Request $request)
    {
        $request->validate([
            'story_id' => 'required|exists:stories,id',
        ]);

        $userId = Auth::id();
        $storyId = $request->story_id;
        
        $bookmark = Bookmark::where('user_id', $userId) 
                           ->where('story_id', $storyId)
                           ->first();
        
        if ($bookmark) {
            $bookmark->delete();
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa bookmark'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy bookmark'
        ]);
    }
}
