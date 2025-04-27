<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Story;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class StoryController extends Controller
{

    private function processAndSaveImage($imageFile)
    {
        $now = Carbon::now();
        $yearMonth = $now->format('Y/m');
        $timestamp = $now->format('YmdHis');
        $randomString = Str::random(8);
        $fileName = "{$timestamp}_{$randomString}";

        // Create directories if they don't exist
        Storage::disk('public')->makeDirectory("covers/{$yearMonth}/original");
        Storage::disk('public')->makeDirectory("covers/{$yearMonth}/medium");
        Storage::disk('public')->makeDirectory("covers/{$yearMonth}/thumbnail");

        // Process original image
        $originalImage = Image::make($imageFile);
        $originalImage->encode('webp', 90);
        Storage::disk('public')->put(
            "covers/{$yearMonth}/original/{$fileName}.webp",
            $originalImage->stream()
        );

        // Process medium size (400x300)
        $mediumImage = Image::make($imageFile);
        $mediumImage->fit(400, 300, function ($constraint) {
            $constraint->aspectRatio();
        });
        $mediumImage->encode('webp', 80);
        Storage::disk('public')->put(
            "covers/{$yearMonth}/medium/{$fileName}.webp",
            $mediumImage->stream()
        );

        // Process thumbnail (60x80)
        $thumbnailImage = Image::make($imageFile);
        $thumbnailImage->fit(60, 80, function ($constraint) {
            $constraint->aspectRatio();
        });
        $thumbnailImage->encode('webp', 70);
        Storage::disk('public')->put(
            "covers/{$yearMonth}/thumbnail/{$fileName}.webp",
            $thumbnailImage->stream()
        );

        return [
            'original' => "covers/{$yearMonth}/original/{$fileName}.webp",
            'medium' => "covers/{$yearMonth}/medium/{$fileName}.webp",
            'thumbnail' => "covers/{$yearMonth}/thumbnail/{$fileName}.webp"
        ];
    }

    public function index(Request $request)
    {
        $query = Story::with(['user', 'categories'])
            ->withCount('chapters');

        // Get counts before applying filters
        $totalStories = Story::count();
        $publishedStories = Story::where('status', 'published')->count();
        $draftStories = Story::where('status', 'draft')->count();

        // Apply status filter
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Apply category filter
        if ($request->category) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category);
            });
        }

        // Apply search filter
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $stories = $query->latest()->paginate(15)
            ->withQueryString();

        // Get categories for filter dropdown
        $categories = Category::all();

        return view('admin.pages.story.index', compact(
            'stories',
            'categories',
            'totalStories',
            'publishedStories',
            'draftStories'
        ));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.pages.story.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|unique:stories|max:255',
            'description' => 'required',
            'categories' => 'required|array|max:4',
            'categories.*' => 'exists:categories,id',
            'cover' => 'required|image|mimes:jpeg,png,jpg,gif',
            'status' => 'required|in:draft,published',
            'link_aff' => 'nullable|url'
        ], [
            'title.required' => 'Tiêu đề không được để trống.',
            'title.unique' => 'Tiêu đề đã tồn tại.',
            'title.max' => 'Tiêu đề không được quá 255 ký tự.',
            'description.required' => 'Mô tả không được để trống.',
            'categories.required' => 'Chuyên mục không được để trống.',
            'categories.array' => 'Chuyên mục phải là một mảng.',
            'categories.max' => 'Chuyên mục không được chọn quá 4.',
            'categories.*.exists' => 'Chuyên mục không hợp lệ.',
            'cover.required' => 'Ảnh bìa không được để trống.',
            'cover.image' => 'Ảnh bìa phải là ảnh.',
            'cover.mimes' => 'Ảnh bìa phải có định dạng jpeg, png, jpg hoặc gif.',
            'status.required' => 'Trạng thái không được để trống.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'link_aff.url' => 'Link liên kết không hợp lệ.'
        ]);

        DB::beginTransaction();
        try {
            $coverPaths = $this->processAndSaveImage($request->file('cover'));

            $story = Story::create([
                'user_id' => auth()->id(),
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'description' => $request->description,
                'status' => $request->status,
                'cover' => $coverPaths['original'],
                'cover_medium' => $coverPaths['medium'],
                'cover_thumbnail' => $coverPaths['thumbnail'],
                'link_aff' => $request->link_aff,
            ]);

            $story->categories()->attach($request->categories);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($coverPaths)) {
                Storage::disk('public')->delete([
                    $coverPaths['original'],
                    $coverPaths['medium'],
                    $coverPaths['thumbnail']
                ]);
            }

            \Log::error('Error creating story:', ['error' => $e->getMessage()]);
            return redirect()->route('stories.create')
                ->with('error', 'Có lỗi xảy ra khi tạo truyện.')->withInput();
        }

        return redirect()->route('stories.index')
            ->with('success', 'Truyện đã được tạo thành công.');
    }

    public function edit(Story $story)
    {
        $categories = Category::all();
        return view('admin.pages.story.edit', compact('story', 'categories'));
    }

    public function update(Request $request, Story $story)
    {
        $request->validate([
            'title' => 'required|max:255|unique:stories,title,' . $story->id,
            'description' => 'required',
            'categories' => 'required|array|max:4',
            'categories.*' => 'exists:categories,id',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:draft,published',
            'link_aff' => 'nullable|url'
        ], [
            'title.required' => 'Tiêu đề không được để trống.',
            'title.unique' => 'Tiêu đề đã tồn tại.',
            'title.max' => 'Tiêu đề không được quá 255 ký tự.',
            'description.required' => 'Mô tả không được để trống.',
            'categories.required' => 'Chuyên mục không được để trống.',
            'categories.max' => 'Bạn chỉ được chọn tối đa 4 thể loại.', // Added custom message
            'categories.*.exists' => 'Chuyên mục không hợp lệ.',
            'cover.image' => 'Ảnh bìa phải là ảnh.',
            'cover.mimes' => 'Ảnh bìa phải có định dạng jpeg, png, jpg hoặc gif.',
            'cover.max' => 'Ảnh bìa không được quá 2MB.',
            'status.required' => 'Trạng thái không được để trống.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'link_aff.url' => 'Link liên kết không hợp lệ.'
        ]);

        DB::beginTransaction();
        try {
            $data = [
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'description' => $request->description,
                'status' => $request->status,
                'completed' => $request->has('completed'),
                'link_aff' => $request->link_aff,
            ];

            if ($request->hasFile('cover')) {
                // Delete old images
                $oldImages = [
                    $story->cover,
                    $story->cover_medium,
                    $story->cover_thumbnail
                ];

                // Process and save new images
                $coverPaths = $this->processAndSaveImage($request->file('cover'));

                $data['cover'] = $coverPaths['original'];
                $data['cover_medium'] = $coverPaths['medium'];
                $data['cover_thumbnail'] = $coverPaths['thumbnail'];
            }

            $story->update($data);
            $story->categories()->sync($request->categories);

            DB::commit();
            if (isset($oldImages)) {
                Storage::disk('public')->delete($oldImages);
            }
            return redirect()->route('stories.index')
                ->with('success', 'Truyện đã được cập nhật thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($coverPaths)) {
                Storage::disk('public')->delete([
                    $coverPaths['original'],
                    $coverPaths['medium'],
                    $coverPaths['thumbnail']
                ]);
            }
            \Log::error('Error updating story:', ['error' => $e->getMessage()]);
            return redirect()->route('stories.edit', $story)
                ->with('error', 'Có lỗi xảy ra khi cập nhật truyện.')->withInput();
        }
    }

    public function show(Story $story)
    {
        $chapters = $story->chapters()
            ->latest()
            ->paginate(15);

        $totalChapters = $story->chapters()->count();
        $publishedChapters = $story->chapters()->where('status', 'published')->count();
        $draftChapters = $story->chapters()->where('status', 'draft')->count();

        return view('admin.pages.chapters.index', compact(
            'story',
            'chapters',
            'totalChapters',
            'publishedChapters',
            'draftChapters'
        ));
    }

    public function destroy(Story $story)
    {
        DB::beginTransaction();

        try {
            // Delete related banners first
            $story->banners()->delete();

            // Now delete other relationships
            $story->categories()->detach();

            // Finally delete the story
            $story->delete();

            DB::commit();

            Storage::disk('public')->delete([
                $story->cover,
                $story->cover_medium,
                $story->cover_thumbnail
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting story:', ['error' => $e->getMessage()]);
            return redirect()->route('stories.index')
                ->with('error', 'Có lỗi xảy ra khi xóa truyện.' . $e->getMessage());
        }

        return redirect()->route('stories.index')
            ->with('success', 'Truyện đã được xóa thành công.');
    }
}
