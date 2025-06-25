<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Story;
use App\Models\Bookmark;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\StoryEditRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class AuthorController extends Controller
{
    // Xử lý duyệt truyện (cho admin)
    public function approve(Story $story)
    {
        if (!Auth::user()->isAdmin()) {
            return redirect()->back()->with('error', 'Bạn không có quyền thực hiện chức năng này.');
        }

        $story->approved = true;
        $story->status = 'published';
        $story->save();

        return redirect()->back()->with('success', 'Đã phê duyệt truyện thành công.');
    }

    public function approveEditRequest(StoryEditRequest $editRequest)
    {
        // Code kiểm tra quyền và điều kiện khác giữ nguyên
        
        DB::beginTransaction();
        try {
            // Cập nhật truyện với dữ liệu từ yêu cầu chỉnh sửa
            $story = $editRequest->story;
            $story->update([
                'title' => $editRequest->title,
                'slug' => $editRequest->slug,
                'description' => $editRequest->description,
                'author_name' => $editRequest->author_name,
                'story_type' => $editRequest->story_type,
                'is_18_plus' => $editRequest->is_18_plus, // Thêm dòng này
                // Các trường khác giữ nguyên
            ]);
            
            // Phần code xử lý còn lại giữ nguyên
            
            DB::commit();
            return redirect()->back()->with('success', 'Đã phê duyệt yêu cầu chỉnh sửa.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error approving edit request: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi phê duyệt yêu cầu chỉnh sửa');
        }
    }
}