<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Story;
use App\Models\Bookmark;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
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
}