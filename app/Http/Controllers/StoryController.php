<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Story;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        // Process original image (WebP)
        $originalImage = Image::make($imageFile);
        $originalImage->encode('webp', 90);
        Storage::disk('public')->put(
            "covers/{$yearMonth}/original/{$fileName}.webp",
            $originalImage->stream()
        );

        // Process original image (JPEG for social media)
        $originalImageJpeg = Image::make($imageFile);
        $originalImageJpeg->encode('jpg', 90);
        Storage::disk('public')->put(
            "covers/{$yearMonth}/original/{$fileName}.jpg",
            $originalImageJpeg->stream()
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
            'original_jpeg' => "covers/{$yearMonth}/original/{$fileName}.jpg",
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
        $featuredStories = Story::where('is_featured', true)->count(); // NEW

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

        // Apply featured filter - NEW
        if ($request->featured !== null && $request->featured !== '') {
            $query->where('is_featured', (bool) $request->featured);
        }

        // Apply search filter
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('author_name', 'like', "%{$search}%");
            });
        }

        // Order by featured first, then by latest - NEW
        $query->orderBy('is_featured', 'desc')
            ->orderBy('featured_order', 'asc')
            ->orderBy('created_at', 'desc');

        $stories = $query->paginate(15);

        // Add query params to pagination links
        if ($request->hasAny(['status', 'category', 'search', 'featured'])) {
            $stories->appends($request->only(['status', 'category', 'search', 'featured']));
        }

        // Get categories for filter dropdown
        $categories = Category::all();

        return view('admin.pages.story.index', compact(
            'stories',
            'categories',
            'totalStories',
            'publishedStories',
            'draftStories',
            'featuredStories' // NEW
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
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
            'cover' => 'required|image|mimes:jpeg,png,jpg,gif',
            'status' => 'required|in:draft,published',
            'link_aff' => 'nullable|url',
            'combo_price' => 'required_if:has_combo,on|nullable|integer|min:0',
            'author_name' => 'nullable|string|max:100',
            'translator_name' => 'nullable|string|max:100',
            'story_type' => 'nullable|string|in:original,translated,rewritten',
            'featured_order' => 'nullable|integer|min:1', // NEW
        ], [
            'title.required' => 'Tiêu đề không được để trống.',
            'title.unique' => 'Tiêu đề đã tồn tại.',
            'title.max' => 'Tiêu đề không được quá 255 ký tự.',
            'description.required' => 'Mô tả không được để trống.',
            'categories.required' => 'Chuyên mục không được để trống.',
            'categories.array' => 'Chuyên mục phải là một mảng.',
            'categories.*.exists' => 'Chuyên mục không hợp lệ.',
            'cover.required' => 'Ảnh bìa không được để trống.',
            'cover.image' => 'Ảnh bìa phải là ảnh.',
            'cover.mimes' => 'Ảnh bìa phải có định dạng jpeg, png, jpg hoặc gif.',
            'status.required' => 'Trạng thái không được để trống.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'link_aff.url' => 'Link liên kết không hợp lệ.',
            'combo_price.required_if' => 'Vui lòng nhập giá combo.',
            'combo_price.integer' => 'Giá combo phải là số nguyên.',
            'combo_price.min' => 'Giá combo không được âm.',
            'author_name.max' => 'Tên tác giả không được quá 100 ký tự.',
            'translator_name.max' => 'Tên dịch giả không được quá 100 ký tự.',
            'story_type.in' => 'Loại truyện không hợp lệ.',
            'featured_order.integer' => 'Thứ tự đề cử phải là số nguyên.',
            'featured_order.min' => 'Thứ tự đề cử phải lớn hơn 0.',
        ]);

        DB::beginTransaction();
        try {
            $coverPaths = $this->processAndSaveImage($request->file('cover'));

            // Set has_combo based on checkbox
            $hasCombo = $request->has('has_combo');
            $comboPrice = $hasCombo ? $request->combo_price : 0;

            // Handle featured - NEW
            $isFeatured = $request->has('is_featured');
            $featuredOrder = null;

            if ($isFeatured) {
                if ($request->featured_order) {
                    // Check if order already exists
                    $existingStory = Story::where('featured_order', $request->featured_order)
                        ->where('is_featured', true)
                        ->first();
                    if ($existingStory) {
                        throw new \Exception('Thứ tự đề cử ' . $request->featured_order . ' đã được sử dụng bởi truyện khác.');
                    }
                    $featuredOrder = $request->featured_order;
                } else {
                    // Auto assign next order
                    $featuredOrder = Story::getNextFeaturedOrder();
                }
            }

            $story = Story::create([
                'user_id' => auth()->id(),
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'description' => $request->description,
                'status' => $request->status,
                'cover' => $coverPaths['original'],
                'cover_jpeg' => $coverPaths['original_jpeg'],
                'cover_medium' => $coverPaths['medium'],
                'cover_thumbnail' => $coverPaths['thumbnail'],
                'link_aff' => $request->link_aff,
                'has_combo' => $hasCombo,
                'combo_price' => $comboPrice,
                'author_name' => $request->author_name,
                'translator_name' => $request->translator_name,
                'story_type' => $request->story_type,
                'is_18_plus' => $request->has('is_18_plus'),
                'is_monopoly' => $request->has('is_monopoly'),
                'is_featured' => $isFeatured, // NEW
                'featured_order' => $featuredOrder, // NEW
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

            Log::error('Error creating story:', ['error' => $e->getMessage()]);
            return redirect()->route('stories.create')
                ->with('error', 'Có lỗi xảy ra khi tạo truyện: ' . $e->getMessage())->withInput();
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
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:draft,published',
            'link_aff' => 'nullable|url',
            'combo_price' => 'required_if:has_combo,on|nullable|integer|min:0',
            'author_name' => 'nullable|string|max:100',
            'translator_name' => 'nullable|string|max:100',
            'story_type' => 'nullable|string|in:original,translated,rewritten',
            'featured_order' => 'nullable|integer|min:1', // NEW
        ], [
            'title.required' => 'Tiêu đề không được để trống.',
            'title.unique' => 'Tiêu đề đã tồn tại.',
            'title.max' => 'Tiêu đề không được quá 255 ký tự.',
            'description.required' => 'Mô tả không được để trống.',
            'categories.required' => 'Chuyên mục không được để trống.',
            'categories.*.exists' => 'Chuyên mục không hợp lệ.',
            'cover.image' => 'Ảnh bìa phải là ảnh.',
            'cover.mimes' => 'Ảnh bìa phải có định dạng jpeg, png, jpg hoặc gif.',
            'cover.max' => 'Ảnh bìa không được quá 2MB.',
            'status.required' => 'Trạng thái không được để trống.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'link_aff.url' => 'Link liên kết không hợp lệ.',
            'combo_price.required_if' => 'Vui lòng nhập giá combo.',
            'combo_price.integer' => 'Giá combo phải là số nguyên.',
            'combo_price.min' => 'Giá combo không được âm.',
            'author_name.max' => 'Tên tác giả không được quá 100 ký tự.',
            'translator_name.max' => 'Tên dịch giả không được quá 100 ký tự.',
            'story_type.in' => 'Loại truyện không hợp lệ.',
            'featured_order.integer' => 'Thứ tự đề cử phải là số nguyên.',
            'featured_order.min' => 'Thứ tự đề cử phải lớn hơn 0.',
        ]);

        DB::beginTransaction();
        try {
            // Set has_combo based on checkbox
            $hasCombo = $request->has('has_combo');
            $comboPrice = $hasCombo ? $request->combo_price : 0;

            // Handle featured - NEW
            $isFeatured = $request->has('is_featured');
            $featuredOrder = $story->featured_order; // Keep current order by default

            if ($isFeatured) {
                if ($request->featured_order && $request->featured_order != $story->featured_order) {
                    // Check if new order already exists
                    $existingStory = Story::where('featured_order', $request->featured_order)
                        ->where('is_featured', true)
                        ->where('id', '!=', $story->id)
                        ->first();
                    if ($existingStory) {
                        throw new \Exception('Thứ tự đề cử ' . $request->featured_order . ' đã được sử dụng bởi truyện khác.');
                    }
                    $featuredOrder = $request->featured_order;
                } elseif (!$story->is_featured) {
                    // Story becoming featured for first time
                    $featuredOrder = $request->featured_order ?: Story::getNextFeaturedOrder();
                }
            } else {
                // Story no longer featured
                $featuredOrder = null;
            }

            $data = [
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'description' => $request->description,
                'status' => $request->status,
                'completed' => $request->has('completed'),
                'link_aff' => $request->link_aff,
                'has_combo' => $hasCombo,
                'combo_price' => $comboPrice,
                'author_name' => $request->author_name,
                'translator_name' => $request->translator_name,
                'story_type' => $request->story_type,
                'is_18_plus' => $request->has('is_18_plus'),
                'is_monopoly' => $request->has('is_monopoly'),
                'is_featured' => $isFeatured, // NEW
                'featured_order' => $featuredOrder, // NEW
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
                $data['cover_jpeg'] = $coverPaths['original_jpeg'];
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
            Log::error('Error updating story:', ['error' => $e->getMessage()]);
            return redirect()->route('stories.edit', $story)
                ->with('error', 'Có lỗi xảy ra khi cập nhật truyện: ' . $e->getMessage())->withInput();
        }
    }

    public function toggleFeatured(Story $story)
    {
        DB::beginTransaction();
        try {
            if ($story->is_featured) {
                // Remove from featured
                $story->update([
                    'is_featured' => false,
                    'featured_order' => null
                ]);
                $message = "Đã bỏ đề cử truyện '{$story->title}'.";
            } else {
                // Add to featured
                $story->update([
                    'is_featured' => true,
                    'featured_order' => Story::getNextFeaturedOrder()
                ]);
                $message = "Đã đặt truyện '{$story->title}' làm truyện đề cử.";
            }

            DB::commit();

            return redirect()->route('stories.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('stories.index')
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Bulk update featured status - NEW METHOD
     */
    public function bulkUpdateFeatured(Request $request)
    {
        $request->validate([
            'story_ids' => 'required|array',
            'story_ids.*' => 'exists:stories,id',
            'action' => 'required|in:feature,unfeature',
        ]);

        DB::beginTransaction();
        try {
            if ($request->action === 'feature') {
                // Feature selected stories
                $nextOrder = Story::getNextFeaturedOrder();

                foreach ($request->story_ids as $storyId) {
                    Story::where('id', $storyId)->update([
                        'is_featured' => true,
                        'featured_order' => $nextOrder++
                    ]);
                }

                $message = 'Đã đặt ' . count($request->story_ids) . ' truyện làm truyện đề cử.';
            } else {
                // Unfeature selected stories
                Story::whereIn('id', $request->story_ids)->update([
                    'is_featured' => false,
                    'featured_order' => null
                ]);

                $message = 'Đã bỏ đề cử ' . count($request->story_ids) . ' truyện.';
            }

            DB::commit();

            return redirect()->route('stories.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('stories.index')
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function show(Story $story)
    {
        // Load story with relationships
        $story->load(['user', 'categories']);
        $story->loadCount('chapters');

        // Get story purchase data
        $story_purchases = $story->purchases()
            ->with('user')
            ->latest()
            ->paginate(10, ['*'], 'story_page');
        $story_purchases_count = $story->purchases()->count();

        // Get chapter purchase data for this story's chapters
        $chapter_purchases = \App\Models\ChapterPurchase::whereHas('chapter', function ($query) use ($story) {
            $query->where('story_id', $story->id);
        })->with(['user', 'chapter'])
            ->latest()
            ->paginate(10, ['*'], 'chapter_page');
        $chapter_purchases_count = \App\Models\ChapterPurchase::whereHas('chapter', function ($query) use ($story) {
            $query->where('story_id', $story->id);
        })->count();

        // Get bookmark data
        $bookmarks = $story->bookmarks()
            ->with(['user', 'lastChapter'])
            ->latest()
            ->paginate(10, ['*'], 'bookmark_page');
        $bookmarks_count = $story->bookmarks()->count();

        // Calculate total revenue (story purchases + chapter purchases)
        $story_revenue = $story->purchases()->sum('amount_paid');
        $chapter_revenue = \App\Models\ChapterPurchase::whereHas('chapter', function ($query) use ($story) {
            $query->where('story_id', $story->id);
        })->sum('amount_paid');
        $total_revenue = $story_revenue + $chapter_revenue;

        return view('admin.pages.story.show', compact(
            'story',
            'story_purchases',
            'story_purchases_count',
            'chapter_purchases',
            'chapter_purchases_count',
            'bookmarks',
            'bookmarks_count',
            'total_revenue'
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
            Log::error('Error deleting story:', ['error' => $e->getMessage()]);
            return redirect()->route('stories.index')
                ->with('error', 'Có lỗi xảy ra khi xóa truyện.');
        }

        return redirect()->route('stories.index')
            ->with('success', 'Truyện đã được xóa thành công.');
    }
}
