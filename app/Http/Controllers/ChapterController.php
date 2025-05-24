<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Models\Story;
use App\Models\Rating;
use App\Models\Status;
use App\Models\Chapter;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\Return_;

class ChapterController extends Controller
{
    public function index(Request $request, Story $story)
    {
       
        $search = $request->search;
        $status = $request->status;
        $query = $story->chapters();

        $totalChapters = $query->count();
        $publishedChapters = $story->chapters()->where('status', 'published')->count();
        $draftChapters = $story->chapters()->where('status', 'draft')->count();

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $searchNumber = preg_replace('/[^0-9]/', '', $search);

            $query->where(function ($q) use ($search, $searchNumber) {
                $q->where('title', 'like', "%$search%")
                    ->orWhere('number', 'like', "%$search%");

                if (is_numeric($searchNumber)) {
                    $q->orWhere('number', '=', (int)$searchNumber);
                }
            });
        }

        $chapters = $query->orderBy('number', 'DESC')->paginate(15);

        foreach ($chapters as $chapter) {
            $content = strip_tags($chapter->content);
            $chapter->content = mb_substr($content, 0, 97, 'UTF-8') . '...';
        }

        return view('admin.pages.chapters.index', compact(
            'story',
            'chapters',
            'totalChapters',
            'publishedChapters',
            'draftChapters',
        ));
    }

    public function create(Story $story)
    {
        $latestChapterNumber = $story->chapters()->max('number') ?? 0;
        $nextChapterNumber = $latestChapterNumber + 1;

        return view('admin.pages.chapters.create', compact('story', 'nextChapterNumber'));
    }

    public function store(Request $request, Story $story)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'number' => [
                'required',
                function ($attribute, $value, $fail) use ($story) {
                    if ($story->chapters()->where('number', $value)->exists()) {
                        $fail('Chương ' . $value . ' đã tồn tại trong truyện này');
                    }
                },
                'integer',
            ],
            'status' => 'required|in:draft,published',
            'link_aff' => 'nullable|url',
            'price' => 'required_if:is_free,0|nullable|integer|min:0',
        ], [
            'title.required' => 'Tên chương không được để trống',
            'content.required' => 'Nội dung chương không được để trống',
            'number.required' => 'Số chương không được để trống',
            'number.integer' => 'Số chương phải là số nguyên',
            'status.required' => 'Trạng thái chương không được để trống',
            'status.in' => 'Trạng thái chương không hợp lệ',
            'link_aff.url' => 'Link liên kết không hợp lệ',
            'price.required_if' => 'Vui lòng nhập giá cho chương này',
            'price.integer' => 'Giá phải là số nguyên',
            'price.min' => 'Giá không được âm',
        ]);

        try {
            // Set is_free based on checkbox
            $isFree = $request->has('is_free');
            
            // If chapter is free, price is 0
            $price = $isFree ? 0 : $request->price;

            $chapter = $story->chapters()->create([
                'slug' => 'chuong-' . $request->number . '-' . Str::slug($request->title),
                'title' => $request->title,
                'content' => $request->content,
                'number' => $request->number,
                'status' => $request->status,
                'user_id' => auth()->id(),
                'updated_content_at' => now(),
                'link_aff' => $request->link_aff,
                'is_free' => $isFree,
                'price' => $price,
            ]);

            return redirect()->route('stories.chapters.index', $story)
                ->with('success', 'Tạo chương ' . $request->number . ' thành công');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra, vui lòng thử lại')
                ->withInput();
        }
    }

    public function edit(Story $story, Chapter $chapter)
    {
        return view('admin.pages.chapters.edit', compact('story', 'chapter'));
    }

    public function update(Request $request, Story $story, Chapter $chapter)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'number' => [
                'required',
                function ($attribute, $value, $fail) use ($story, $chapter) {
                    if ($story->chapters()
                        ->where('number', $value)
                        ->where('id', '!=', $chapter->id)
                        ->exists()
                    ) {
                        $fail('Chương số ' . $value . ' đã tồn tại trong truyện này');
                    }
                },
                'integer',
            ],
            'status' => 'required|in:draft,published',
            'link_aff' => 'nullable|url',
            'price' => 'required_if:is_free,0|nullable|integer|min:0',
        ],[
            'title.required' => 'Tên chương không được để trống',
            'content.required' => 'Nội dung chương không được để trống',
            'number.required' => 'Số chương không được để trống',
            'number.integer' => 'Số chương phải là số nguyên',
            'status.required' => 'Trạng thái chương không được để trống',
            'status.in' => 'Trạng thái chương không hợp lệ',
            'link_aff.url' => 'Link liên kết không hợp lệ',
            'price.required_if' => 'Vui lòng nhập giá cho chương này',
            'price.integer' => 'Giá phải là số nguyên',
            'price.min' => 'Giá không được âm',
        ]);

        try {
            // Set is_free based on checkbox
            $isFree = $request->has('is_free');
            
            // If chapter is free, price is 0
            $price = $isFree ? 0 : $request->price;

            $chapter->update([
                'slug' => 'chuong-' . $request->number . '-' . Str::slug($request->title),
                'title' => $request->title,
                'content' => $request->content,
                'number' => $request->number,
                'status' => $request->status,
                'updated_content_at' => now(),
                'link_aff' => $request->link_aff,
                'is_free' => $isFree,
                'price' => $price,
            ]);

            return redirect()->route('stories.chapters.index', $story)
                ->with('success', 'Cập nhật chương ' . $request->number . ' thành công');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra, vui lòng thử lại')
                ->withInput();
        }
    }

    public function destroy(Story $story, Chapter $chapter)
    {
        try {
            $chapter->delete();
            return redirect()->route('stories.chapters.index', $story)
                ->with('success', 'Xóa chương thành công');
        } catch (\Exception $e) {
            return redirect()->route('stories.chapters.index', $story)
                ->with('error', 'Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
