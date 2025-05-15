<?php

namespace App\Http\Controllers;

use App\Models\Story;
use App\Models\StoryCombo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class StoryComboController extends Controller
{
    /**
     * Show form to create a new story combo
     */
    public function create(Story $story)
    {
        // Kiểm tra quyền sở hữu
        if ($story->user_id !== Auth::id()) {
            return redirect()->route('user.author.stories')
                ->with('error', 'Bạn không có quyền tạo combo cho truyện này.');
        }

        // Kiểm tra trạng thái truyện
        if (!$story->getCanCreateComboAttribute()) {
            return redirect()->route('user.author.stories.chapters', $story->id)
                ->with('error', 'Chỉ những truyện đã hoàn thành (full) mới có thể tạo combo.');
        }

        // Kiểm tra nếu truyện đã có combo
        if ($story->hasCombo()) {
            return redirect()->route('user.author.stories.combo.edit', $story->id)
                ->with('info', 'Truyện này đã có combo. Bạn có thể chỉnh sửa combo hiện tại.');
        }

        // Tính toán giá trị mặc định
        $totalChapterPrice = $story->getTotalChapterPriceAttribute();

        return view('pages.information.author.combo.author_combo_create', compact('story', 'totalChapterPrice'));
    }

    /**
     * Store a newly created story combo
     */
    public function store(Request $request, Story $story)
    {
        // Kiểm tra quyền sở hữu
        if ($story->user_id !== Auth::id()) {
            return redirect()->route('user.author.stories')
                ->with('error', 'Bạn không có quyền tạo combo cho truyện này.');
        }

        // Kiểm tra trạng thái truyện
        if (!$story->getCanCreateComboAttribute()) {
            return redirect()->route('user.author.stories.chapters', $story->id)
                ->with('error', 'Chỉ những truyện đã hoàn thành (full) mới có thể tạo combo.');
        }

        // Kiểm tra nếu truyện đã có combo
        if ($story->hasCombo()) {
            return redirect()->route('user.author.stories.combo.edit', $story->id)
                ->with('info', 'Truyện này đã có combo. Bạn có thể chỉnh sửa combo hiện tại.');
        }

        $totalChapterPrice = $story->getTotalChapterPriceAttribute();

        // Validate input
        $request->validate([
            'combo_price' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($totalChapterPrice) {
                    // Giá combo phải thấp hơn tổng giá các chương
                    if ($value >= $totalChapterPrice) {
                        $fail('Giá combo phải thấp hơn tổng giá các chương riêng lẻ (' . $totalChapterPrice . ' xu).');
                    }
                },
            ],
        ], [
            'combo_price.required' => 'Vui lòng nhập giá combo',
            'combo_price.integer' => 'Giá combo phải là số nguyên',
            'combo_price.min' => 'Giá combo phải lớn hơn 0',
        ]);

        try {
            // Tạo combo mới
            $story->update([
                'has_combo' => true,
                'combo_price' => $request->combo_price,
            ]);


            return redirect()->route('user.author.stories.chapters', $story->id)
                ->with('success', 'Đã tạo combo thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing the specified combo
     */
    public function edit(Story $story)
    {
        // Kiểm tra quyền sở hữu
        if ($story->user_id !== Auth::id()) {
            return redirect()->route('user.author.stories')
                ->with('error', 'Bạn không có quyền chỉnh sửa combo này.');
        }

        // Kiểm tra nếu combo không tồn tại
        if (!$story->hasCombo()) {
            return redirect()->route('user.author.stories.combo.create', $story->id)
                ->with('info', 'Truyện này chưa có combo. Bạn có thể tạo combo mới.');
        }

        $totalChapterPrice = $story->getTotalChapterPriceAttribute();

        return view('pages.information.author.combo.author_combo_edit', compact('story', 'totalChapterPrice'));
    }

    /**
     * Update the specified combo
     */
    public function update(Request $request, Story $story)
    {

        

        // Kiểm tra quyền sở hữu
        if ($story->user_id !== Auth::id()) {
            return redirect()->route('user.author.stories')
                ->with('error', 'Bạn không có quyền chỉnh sửa combo này.');
        }

      
        if (!$story->hasCombo()) {
            return redirect()->route('user.author.stories.combo.create', $story->id)
                ->with('info', 'Truyện này chưa có combo. Bạn có thể tạo combo mới.');
        }

        $totalChapterPrice = $story->getTotalChapterPriceAttribute();

        // Validate input
        $request->validate([
            'combo_price' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($totalChapterPrice) {
                    // Giá combo phải thấp hơn tổng giá các chương
                    if ($value >= $totalChapterPrice) {
                        $fail('Giá combo phải thấp hơn tổng giá các chương riêng lẻ (' . $totalChapterPrice . ' xu).');
                    }
                },
            ],
        ], [
            'combo_price.required' => 'Vui lòng nhập giá combo',
            'combo_price.integer' => 'Giá combo phải là số nguyên',
            'combo_price.min' => 'Giá combo phải lớn hơn 0',
        ]);

        
        try {
            // Cập nhật combo
            $story->update([
                'combo_price' => $request->combo_price,
                'has_combo' => $request->has('has_combo'),
            ]);
            
            return redirect()->route('user.author.stories.chapters', $story->id)
                ->with('success', 'Đã cập nhật combo thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified combo
     */
    public function destroy(Story $story)
    {
        // Kiểm tra quyền sở hữu
        if ($story->user_id !== Auth::id()) {
            return redirect()->route('user.author.stories')
                ->with('error', 'Bạn không có quyền xóa combo này.');
        }

        // Kiểm tra nếu combo không tồn tại
        if (!$story->hasCombo()) {
            return redirect()->route('user.author.stories.chapters', $story->id)
                ->with('error', 'Truyện này không có combo nào để xóa.');
        }

        try {
            // Xóa combo
            $story->update([
                'has_combo' => false,
                'combo_price' => null,
            ]);

            return redirect()->route('user.author.stories.chapters', $story->id)
                ->with('success', 'Đã xóa combo thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}
