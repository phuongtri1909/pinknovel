<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Story;
use App\Models\Chapter;
use App\Models\Bookmark;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\StoryEditRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class AuthorController extends Controller
{
    // Hiển thị danh sách truyện của tác giả
    public function index()
    {
        // Dashboard của tác giả - Trang chính
        $stories = auth()->user()->stories()->latest()->paginate(5);
        $pendingCount = auth()->user()->stories()->where('status', 'pending')->count();

        // Thêm các biến thống kê cho dashboard
        $totalViews = Chapter::whereIn('story_id', auth()->user()->stories()->pluck('id'))->sum('views');
        $totalChapters = auth()->user()->stories()->withCount('chapters')->get()->sum('chapters_count');
        $totalComments = auth()->user()->stories()->withCount('comments')->get()->sum('comments_count');
        $totalFollowers = Bookmark::whereIn('story_id', auth()->user()->stories()->pluck('id'))->count();

        // Lấy doanh thu của tháng hiện tại
        $totalRevenue = DB::table('chapter_purchases')
            ->join('chapters', 'chapter_purchases.chapter_id', '=', 'chapters.id')
            ->join('stories', 'chapters.story_id', '=', 'stories.id')
            ->where('stories.user_id', Auth::id())
            ->whereMonth('chapter_purchases.created_at', date('m'))
            ->whereYear('chapter_purchases.created_at', date('Y'))
            ->sum('chapter_purchases.amount_received');

        // Cộng thêm doanh thu từ việc bán trọn bộ trong tháng hiện tại
        $totalRevenue += DB::table('story_purchases')
            ->join('stories', 'story_purchases.story_id', '=', 'stories.id')
            ->where('stories.user_id', Auth::id())
            ->whereMonth('story_purchases.created_at', date('m'))
            ->whereYear('story_purchases.created_at', date('Y'))
            ->sum('story_purchases.amount_received');

        // Lấy doanh thu của tháng trước để so sánh
        $lastMonthDate = \Carbon\Carbon::now()->subMonth();
        $lastMonthRevenue = DB::table('chapter_purchases')
            ->join('chapters', 'chapter_purchases.chapter_id', '=', 'chapters.id')
            ->join('stories', 'chapters.story_id', '=', 'stories.id')
            ->where('stories.user_id', Auth::id())
            ->whereMonth('chapter_purchases.created_at', $lastMonthDate->month)
            ->whereYear('chapter_purchases.created_at', $lastMonthDate->year)
            ->sum('chapter_purchases.amount_received');

        // Cộng thêm doanh thu từ việc bán trọn bộ trong tháng trước
        $lastMonthRevenue += DB::table('story_purchases')
            ->join('stories', 'story_purchases.story_id', '=', 'stories.id')
            ->where('stories.user_id', Auth::id())
            ->whereMonth('story_purchases.created_at', $lastMonthDate->month)
            ->whereYear('story_purchases.created_at', $lastMonthDate->year)
            ->sum('story_purchases.amount_received');

        // Tính tỷ lệ thay đổi
        $revenueChange = 0;
        $revenueChangePercent = 0;
        $revenueIncreased = false;

        if ($lastMonthRevenue > 0) {
            $revenueChange = $totalRevenue - $lastMonthRevenue;
            $revenueChangePercent = round(($revenueChange / $lastMonthRevenue) * 100);
            $revenueIncreased = $revenueChange > 0;
        } elseif ($totalRevenue > 0) {
            // Nếu tháng trước không có doanh thu nhưng tháng này có
            $revenueChangePercent = 100;
            $revenueIncreased = true;
        }

        // Có thể thêm hoạt động gần đây nếu bạn có bảng dữ liệu tương ứng
        // $recentActivities = Activity::where('user_id', auth()->id())->latest()->take(5)->get();

        return view('pages.information.author.author', compact(
            'stories',
            'pendingCount',
            'totalViews',
            'totalChapters',
            'totalComments',
            'totalFollowers',
            'totalRevenue',
            'revenueChange',
            'revenueChangePercent',
            'revenueIncreased'
        ));
    }

    public function stories(Request $request)
    {
        // Trang danh sách truyện
        $query = auth()->user()->stories();

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo phê duyệt
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Tìm kiếm theo tên
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $stories = $query->latest()->paginate(10);

        // Thống kê
        $publishedCount = auth()->user()->stories()->where('status', 'published')->count();
        $pendingCount = auth()->user()->stories()->where('status', 'pending')->count();
        $draftCount = auth()->user()->stories()->where('status', 'draft')->count();

        return view('pages.information.author.author_stories', compact(
            'stories',
            'publishedCount',
            'pendingCount',
            'draftCount'
        ));
    }

    // Hiển thị form tạo truyện mới
    public function create()
    {
        return view('pages.information.author.author_create');
    }

    // Xử lý lưu truyện mới
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|unique:stories|max:255',
            'description' => 'required',
            'category_input' => 'required',
            'cover' => 'required|image|mimes:jpeg,png,jpg,gif',
            'author_name' => 'required|max:255',
            'story_type' => 'required|in:collected,original,translated',
            'translator_name' => 'nullable|max:255',
            'is_18_plus' => 'nullable|boolean',
            'is_monopoly' => 'nullable|boolean',
        ], [
            'title.required' => 'Tiêu đề không được để trống.',
            'title.unique' => 'Tiêu đề đã tồn tại.',
            'title.max' => 'Tiêu đề không được quá 255 ký tự.',
            'description.required' => 'Mô tả không được để trống.',
            'category_input.required' => 'Thể loại không được để trống.',
            'cover.required' => 'Ảnh bìa không được để trống.',
            'cover.image' => 'Ảnh bìa phải là ảnh.',
            'cover.mimes' => 'Ảnh bìa phải có định dạng jpeg, png, jpg hoặc gif.',
            'author_name.required' => 'Tên tác giả không được để trống.',
            'author_name.max' => 'Tên tác giả không được quá 255 ký tự.',
            'story_type.required' => 'Loại truyện không được để trống.',
            'story_type.in' => 'Loại truyện không hợp lệ.',
            'is_18_plus.boolean' => 'Trạng thái 18+ không hợp lệ.',
            'translator_name.max' => 'Tên người dịch không được quá 255 ký tự.',
            'is_monopoly.boolean' => 'Trạng thái độc quyền không hợp lệ.',
        ]);

        DB::beginTransaction();
        try {
            // Xử lý ảnh bìa
            $coverPaths = $this->processAndSaveImage($request->file('cover'));

            // Tạo truyện mới
            $story = Story::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'description' => $request->description,
                'status' => 'draft',
                'cover' => $coverPaths['original'],
                'cover_medium' => $coverPaths['medium'],
                'cover_thumbnail' => $coverPaths['thumbnail'],
                'author_name' => $request->author_name,
                'translator_name' => $request->translator_name,
                'story_type' => $request->story_type,
                'is_18_plus' => $request->has('is_18_plus'),
                'is_monopoly' => $request->has('is_monopoly'),
            ]);

            // Xử lý categories
            $categoryNames = explode(',', $request->category_input);
            $categoryIds = [];

            foreach ($categoryNames as $name) {
                $name = trim($name);
                if (empty($name)) continue;

                $category = Category::firstOrCreate(
                    ['slug' => Str::slug($name)],
                    [
                        'name' => $name,
                        'slug' => Str::slug($name),
                    ]
                );

                $categoryIds[] = $category->id;
            }

            $story->categories()->attach($categoryIds);
            DB::commit();

            // Chuyển hướng đến trang chỉnh sửa truyện thay vì trang danh sách
            return redirect()->route('user.author.stories.edit', $story->id)
                ->with('success', 'Truyện đã được tạo. Vui lòng thêm ít nhất 3 chương trước khi gửi yêu cầu duyệt.');
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($coverPaths)) {
                Storage::disk('public')->delete([
                    $coverPaths['original'],
                    $coverPaths['medium'],
                    $coverPaths['thumbnail']
                ]);
            }

            Log::error('Error creating story: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi tạo truyện')->withInput();
        }
    }

    // Hiển thị form chỉnh sửa truyện
    public function edit(Story $story)
    {
        // Kiểm tra nếu truyện không thuộc về người dùng hiện tại

        if ($story->user_id != Auth::id()) {
            return redirect()->route('user.author.index')
                ->with('error', 'Bạn không có quyền chỉnh sửa truyện này.');
        }

        $categoryNames = $story->categories->pluck('name')->implode(', ');
        return view('pages.information.author.author_edit', compact('story', 'categoryNames'));
    }

    // Xử lý cập nhật truyện
    public function update(Request $request, Story $story)
    {
        // Kiểm tra nếu truyện không thuộc về người dùng hiện tại
        if ($story->user_id != Auth::id()) {
            return redirect()->route('user.author.index')
                ->with('error', 'Bạn không có quyền chỉnh sửa truyện này.');
        }

        $request->validate([
            'title' => 'required|max:255|unique:stories,title,' . $story->id,
            'description' => 'required',
            'category_input' => 'required',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'author_name' => 'required|max:255',
            'story_type' => 'required|in:collected,original,translated',
            'is_18_plus' => 'nullable|boolean',
            'translator_name' => 'nullable|max:255',
            'is_monopoly' => 'nullable|boolean',
        ], [
            'title.required' => 'Tiêu đề không được để trống.',
            'title.unique' => 'Tiêu đề đã tồn tại.',
            'title.max' => 'Tiêu đề không được quá 255 ký tự.',
            'description.required' => 'Mô tả không được để trống.',
            'category_input.required' => 'Thể loại không được để trống.',
            'cover.image' => 'Ảnh bìa phải là ảnh.',
            'cover.mimes' => 'Ảnh bìa phải có định dạng jpeg, png, jpg hoặc gif.',
            'author_name.required' => 'Tên tác giả không được để trống.',
            'author_name.max' => 'Tên tác giả không được quá 255 ký tự.',
            'story_type.required' => 'Loại truyện không được để trống.',
            'story_type.in' => 'Loại truyện không hợp lệ.',
            'is_18_plus.boolean' => 'Trạng thái 18+ không hợp lệ.',
            'translator_name.max' => 'Tên người dịch không được quá 255 ký tự.',
            'is_monopoly.boolean' => 'Trạng thái độc quyền không hợp lệ.',
        ]);

        // Xử lý categories
        $categoryNames = explode(',', $request->category_input);
        $categoryIds = [];
        $categoryData = [];

        foreach ($categoryNames as $name) {
            $name = trim($name);
            if (empty($name)) continue;

            $category = Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'slug' => Str::slug($name),
                ]
            );

            $categoryIds[] = $category->id;
            $categoryData[] = [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug
            ];
        }

        DB::beginTransaction();
        try {
            // UPDATED LOGIC: Check if story is published AND completed
            if ($story->status === 'published' && $story->completed == 1) {
                // Nếu story đã xuất bản VÀ đã hoàn thành, tạo edit request

                // Kiểm tra xem đã có edit request nào đang chờ duyệt chưa
                if ($story->hasPendingEditRequest()) {
                    return redirect()->back()
                        ->with('error', 'Truyện này đã có yêu cầu chỉnh sửa đang chờ duyệt. Vui lòng chờ admin xử lý trước khi gửi yêu cầu mới.');
                }

                // Kiểm tra xem có thay đổi thực sự nào không
                $hasChanges = false;

                // Kiểm tra các trường văn bản có thay đổi không
                if (
                    $story->title !== $request->title ||
                    $story->description !== $request->description ||
                    $story->author_name !== $request->author_name ||
                    $story->translator_name !== $request->translator_name ||
                    $story->story_type !== $request->story_type ||
                    $story->is_18_plus !== $request->has('is_18_plus') ||
                    $story->is_monopoly !== $request->has('is_monopoly')
                ) {
                    $hasChanges = true;
                }

                // Kiểm tra thay đổi ở ảnh
                if ($request->hasFile('cover')) {
                    $hasChanges = true;
                }

                // Kiểm tra thay đổi ở thể loại
                $currentCategoryIds = $story->categories->pluck('id')->toArray();
                sort($currentCategoryIds);
                sort($categoryIds);
                if ($currentCategoryIds != $categoryIds) {
                    $hasChanges = true;
                }

                // Nếu không có thay đổi thực sự, thông báo và quay lại
                if (!$hasChanges) {
                    return redirect()->back()
                        ->with('info', 'Không có thay đổi nào được phát hiện.');
                }

                $editRequestData = [
                    'story_id' => $story->id,
                    'user_id' => Auth::id(),
                    'title' => $request->title,
                    'slug' => Str::slug($request->title),
                    'description' => $request->description,
                    'author_name' => $request->author_name,
                    'story_type' => $request->story_type,
                    'is_18_plus' => $request->has('is_18_plus'),
                    'translator_name' => $request->translator_name,
                    'categories_data' => json_encode($categoryData),
                    'status' => 'pending',
                    'submitted_at' => now(),
                    'is_monopoly' => $request->has('is_monopoly'),
                ];

                // Xử lý ảnh bìa nếu có upload mới
                if ($request->hasFile('cover')) {
                    $coverPaths = $this->processAndSaveImage($request->file('cover'));

                    $editRequestData['cover'] = $coverPaths['original'];
                    $editRequestData['cover_medium'] = $coverPaths['medium'];
                    $editRequestData['cover_thumbnail'] = $coverPaths['thumbnail'];
                }

                // Tạo yêu cầu chỉnh sửa mới
                $editRequest = StoryEditRequest::create($editRequestData);

                DB::commit();

                return redirect()->route('user.author.stories.edit', $story->id)
                    ->with('success', 'Truyện đã hoàn thành nên yêu cầu chỉnh sửa đã được gửi đi và đang chờ admin phê duyệt.');
            } else {
                // UPDATED: Nếu story chưa hoàn thành hoặc chưa xuất bản, cập nhật trực tiếp
                $data = [
                    'title' => $request->title,
                    'slug' => Str::slug($request->title),
                    'description' => $request->description,
                    'author_name' => $request->author_name,
                    'story_type' => $request->story_type,
                    'translator_name' => $request->translator_name,
                    'is_18_plus' => $request->has('is_18_plus'),
                    'is_monopoly' => $request->has('is_monopoly'),
                ];

                // Chỉ thay đổi status thành draft nếu đang pending
                if ($story->status === 'pending') {
                    $data['status'] = 'draft';
                }

                // Xử lý ảnh bìa nếu có upload mới
                if ($request->hasFile('cover')) {
                    // Lưu lại paths ảnh cũ để xóa sau
                    $oldImages = [
                        $story->cover,
                        $story->cover_medium,
                        $story->cover_thumbnail
                    ];

                    // Xử lý ảnh mới
                    $coverPaths = $this->processAndSaveImage($request->file('cover'));

                    $data['cover'] = $coverPaths['original'];
                    $data['cover_medium'] = $coverPaths['medium'];
                    $data['cover_thumbnail'] = $coverPaths['thumbnail'];
                }

                $story->update($data);
                $story->categories()->sync($categoryIds);

                DB::commit();

                // Xóa ảnh cũ nếu có upload mới
                if (isset($oldImages) && isset($coverPaths)) {
                    Storage::disk('public')->delete($oldImages);
                }

                $message = 'Truyện đã được cập nhật thành công.';

                // Thông báo khác nhau tùy theo trạng thái
                if ($story->status === 'published' && $story->completed == 0) {
                    $message .= ' Truyện chưa hoàn thành nên có thể chỉnh sửa tự do mà không cần phê duyệt.';
                } elseif ($story->status !== 'published') {
                    $message .= ' Truyện chưa xuất bản nên có thể chỉnh sửa tự do.';
                }

                return redirect()->route('user.author.stories.edit', $story->id)
                    ->with('success', $message);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            // Xóa ảnh mới nếu có lỗi và đã upload
            if (isset($coverPaths)) {
                Storage::disk('public')->delete([
                    $coverPaths['original'],
                    $coverPaths['medium'],
                    $coverPaths['thumbnail']
                ]);
            }

            Log::error('Error updating story: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi cập nhật truyện')->withInput();
        }
    }

    // Xử lý xóa truyện
    public function destroy(Story $story)
    {
        // Kiểm tra nếu truyện không thuộc về người dùng hiện tại
        if ($story->user_id != Auth::id()) {
            return redirect()->route('user.author.index')
                ->with('error', 'Bạn không có quyền xóa truyện này.');
        }

        DB::beginTransaction();
        try {
            // Xóa các mối quan hệ
            $story->categories()->detach();
            $story->chapters()->delete();

            // Xóa truyện
            $story->delete();

            DB::commit();

            // Xóa các ảnh liên quan
            Storage::disk('public')->delete([
                $story->cover,
                $story->cover_medium,
                $story->cover_thumbnail
            ]);

            return redirect()->route('user.author.stories')
                ->with('success', 'Truyện đã được xóa thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting story: ' . $e->getMessage());
            return redirect()->route('user.author.stories')
                ->with('error', 'Có lỗi xảy ra khi xóa truyện');
        }
    }

    public function markComplete(Story $story)
    {
        // Kiểm tra nếu truyện không thuộc về người dùng hiện tại
        if ($story->user_id != Auth::id()) {
            return redirect()->route('user.author.index')
                ->with('error', 'Bạn không có quyền thực hiện hành động này.');
        }

        // Truyện phải đang được xuất bản
        if ($story->status !== 'published') {
            return redirect()->route('user.author.stories.chapters', $story->id)
                ->with('error', 'Chỉ những truyện đã được xuất bản mới có thể đánh dấu hoàn thành.');
        }

        try {
            $story->update([
                'completed' => true
            ]);

            return redirect()->route('user.author.stories.chapters', $story->id)
                ->with('success', 'Truyện đã được đánh dấu là hoàn thành. Bạn có thể tạo combo trọn bộ ngay bây giờ!');
        } catch (\Exception $e) {
            Log::error('Error marking story as complete: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi đánh dấu truyện là hoàn thành.');
        }
    }

    // Hiển thị danh sách các chương của truyện
    public function showChapters(Story $story)
    {
        // Kiểm tra nếu truyện không thuộc về người dùng hiện tại
        if ($story->user_id != Auth::id()) {
            return redirect()->route('user.author.index')
                ->with('error', 'Bạn không có quyền xem các chương của truyện này.');
        }

        $chapters = $story->chapters()->orderBy('number', 'asc')->paginate(10);
        return view('pages.information.author.author_chapters', compact('story', 'chapters'));
    }

    // Hiển thị form tạo chương mới
    public function createChapter(Story $story)
    {
        // Kiểm tra nếu truyện không thuộc về người dùng hiện tại
        if ($story->user_id != Auth::id()) {
            return redirect()->route('user.author.index')
                ->with('error', 'Bạn không có quyền thêm chương cho truyện này.');
        }

        $latestChapterNumber = $story->chapters()->max('number') ?? 0;
        $nextChapterNumber = $latestChapterNumber + 1;

        return view('pages.information.author.author_chapter_create', compact('story', 'nextChapterNumber'));
    }

    // Hiển thị form tạo nhiều chương cùng lúc
    public function createBatchChapters(Story $story)
    {
        // Kiểm tra nếu truyện không thuộc về người dùng hiện tại
        if ($story->user_id != Auth::id()) {
            return redirect()->route('user.author.index')
                ->with('error', 'Bạn không có quyền thêm chương cho truyện này.');
        }

        $latestChapterNumber = $story->chapters()->max('number') ?? 0;
        $nextChapterNumber = $latestChapterNumber + 1;

        return view('pages.information.author.author_batch_chapter_create', compact('story', 'nextChapterNumber'));
    }

    // Xử lý lưu chương mới
    public function storeChapter(Request $request, Story $story)
    {
        // Kiểm tra nếu truyện không thuộc về người dùng hiện tại
        if ($story->user_id != Auth::id()) {
            return redirect()->route('user.author.index')
                ->with('error', 'Bạn không có quyền thêm chương cho truyện này.');
        }

        // Kiểm tra nếu là nhiều chương (route cũ - để hỗ trợ ngược)
        if ($request->upload_type === 'multiple') {
            return $this->storeBatchChapters($request, $story);
        }

        $request->validate([
            'title' => 'nullable|max:255|unique:chapters,title,' . $story->id . ',story_id',
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
            'is_free' => 'required|boolean',
            'price' => 'required_if:is_free,0|nullable|integer|min:1',
            'password' => 'nullable|required_if:has_password,1|string|max:50',
            'has_password' => 'required_if:is_free,1|boolean',
            'scheduled_publish_at' => 'nullable|date|after:now',
            'status' => 'required|in:draft,published',
        ], [
            'title.required' => 'Tên chương không được để trống',
            'title.unique' => 'Tên chương này đã tồn tại trong truyện',
            'title.max' => 'Tên chương không được quá 255 ký tự',
            'content.required' => 'Nội dung chương không được để trống',
            'number.required' => 'Số chương không được để trống',
            'number.integer' => 'Số chương phải là số nguyên',
            'is_free.required' => 'Vui lòng chọn hình thức chương',
            'price.required_if' => 'Vui lòng nhập giá xu cho chương',
            'price.integer' => 'Giá xu phải là số nguyên',
            'price.min' => 'Giá xu phải lớn hơn 0',
            'password.required_if' => 'Vui lòng nhập mật khẩu cho chương',
            'scheduled_publish_at.date' => 'Thời gian hẹn giờ không hợp lệ',
            'scheduled_publish_at.after' => 'Thời gian hẹn giờ phải sau thời điểm hiện tại',
            'status.required' => 'Vui lòng chọn trạng thái chương',
        ]);

        try {
            // Tạo slug dự kiến để kiểm tra trùng lặp
            $proposedSlug = 'chuong-' . $request->number . '-' . Str::slug(Str::limit($request->title, 100));

            // Kiểm tra xem slug đã tồn tại chưa
            if ($story->chapters()->where('slug', $proposedSlug)->exists()) {
                return redirect()->back()
                    ->with('error', 'Tiêu đề chương này tạo ra slug đã tồn tại. Vui lòng sử dụng tiêu đề khác.')
                    ->withInput();
            }

            // Chỉ sử dụng scheduled_publish_at khi status là draft
            $scheduledPublishAt = null;
            if ($request->status == 'draft' && $request->has('scheduled_publish_at')) {
                $scheduledPublishAt = $request->scheduled_publish_at;
            }

            $chapter = $story->chapters()->create([
                'slug' => $proposedSlug,
                'title' => $request->title ?: 'Chương ' . $request->number,
                'content' => $request->content,
                'number' => $request->number,
                'status' => $request->status,
                'user_id' => Auth::id(),
                'updated_content_at' => now(),
                'is_free' => $request->is_free,
                'price' => !$request->is_free ? $request->price : null,
                'password' => ($request->is_free && $request->has_password) ? bcrypt($request->password) : null,
                'scheduled_publish_at' => $scheduledPublishAt,
            ]);

            return redirect()->route('user.author.stories.chapters', $story->id)
                ->with('success', 'Đã tạo chương ' . $request->number . ' thành công');
        } catch (\Exception $e) {
            Log::error('Error creating chapter: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi tạo chương. Vui lòng thử lại.')
                ->withInput();
        }
    }

    // Hàm phân tích nội dung batch để tách thành các chương
    private function parseChaptersFromBatchContent($batchContent)
    {
        // Updated pattern to handle all variations:
        // 1. "Chương X: Title" (with colon, no space)
        // 2. "Chương X" (without title)
        // 3. Multiple blank lines between chapters
        $pattern = '/Chương\s+(\d+)(?:\s*:\s*([^\r\n]*))?[\r\n]+([\s\S]*?)(?=[\r\n]+Chương\s+\d+|\z)/';

        preg_match_all($pattern, $batchContent, $matches, PREG_SET_ORDER);

        $chapters = [];

        foreach ($matches as $match) {
            $chapterNumber = (int) $match[1];

            // If there's no title or empty title, use "Chương X" as the title
            $title = !empty($match[2]) ? trim($match[2]) : "Chương {$chapterNumber}";

            $content = isset($match[3]) ? trim($match[3]) : '';

            $chapters[] = [
                'number'  => $chapterNumber,
                'title'   => $title,
                'content' => $content,
            ];
        }

        return $chapters;
    }

    // Xử lý lưu nhiều chương cùng lúc
    public function storeBatchChapters(Request $request, Story $story)
    {
        // Kiểm tra nếu truyện không thuộc về người dùng hiện tại
        if ($story->user_id != Auth::id()) {
            return redirect()->route('user.author.index')
                ->with('error', 'Bạn không có quyền thêm chương cho truyện này.');
        }

        $request->validate([
            'batch_content' => 'required',
            'is_free' => 'required|boolean',
            'price' => 'required_if:is_free,0|nullable|integer|min:1',
            'password' => 'nullable|required_if:has_password,1|string|max:50',
            'has_password' => 'required_if:is_free,1|boolean',
            'scheduled_publish_at' => 'nullable|date|after:now',
            'status' => 'required|in:draft,published',
            'chapter_schedules' => 'nullable|array',
            'chapter_schedules.*' => 'nullable|date|after:now',
        ], [
            'batch_content.required' => 'Nội dung các chương không được để trống',
            'is_free.required' => 'Vui lòng chọn hình thức chương',
            'price.required_if' => 'Vui lòng nhập giá xu cho chương',
            'price.integer' => 'Giá xu phải là số nguyên',
            'price.min' => 'Giá xu phải lớn hơn 0',
            'password.required_if' => 'Vui lòng nhập mật khẩu cho chương',
            'scheduled_publish_at.date' => 'Thời gian hẹn giờ không hợp lệ',
            'scheduled_publish_at.after' => 'Thời gian hẹn giờ phải sau thời điểm hiện tại',
            'status.required' => 'Vui lòng chọn trạng thái chương',
            'chapter_schedules.*.date' => 'Thời gian hẹn giờ không hợp lệ',
            'chapter_schedules.*.after' => 'Thời gian hẹn giờ phải sau thời điểm hiện tại',
        ]);

        $chapters = $this->parseChaptersFromBatchContent($request->batch_content);

        if (empty($chapters)) {
            return back()->with('error', 'Không thể tách nội dung thành các chương. Vui lòng kiểm tra lại định dạng.')->withInput();
        }

        $existingNumbers = $story->chapters()->pluck('number')->toArray();
        $existingSlugs = $story->chapters()->pluck('slug')->toArray();

        $errors = [
            'number' => [],
            'slug' => [],
        ];

        foreach ($chapters as $chapter) {
            $slug = 'chuong-' . $chapter['number'] . '-' . Str::slug(Str::limit($chapter['title'], 100));

            if (in_array($chapter['number'], $existingNumbers)) {
                $errors['number'][] = "Chương {$chapter['number']}";
            }

            if (in_array($slug, $existingSlugs)) {
                $errors['slug'][] = "Chương {$chapter['number']} ({$chapter['title']})";
            }
        }

        if (!empty($errors['number']) || !empty($errors['slug'])) {
            $msg = "Phát hiện chương bị trùng lặp:";
            if ($errors['number']) $msg .= " Trùng số chương: " . implode(', ', $errors['number']) . ".";
            if ($errors['slug'])   $msg .= " Trùng slug: " . implode(', ', $errors['slug']) . ".";
            return back()->with('error', $msg . ' Vui lòng chỉnh sửa và thử lại.')->withInput();
        }

        // Lấy dữ liệu lịch đăng cho từng chương
        $chapterSchedules = $request->input('chapter_schedules', []);

        DB::beginTransaction();
        try {
            $userId = Auth::id();
            $isFree = $request->is_free;
            $hasPassword = $request->has_password;
            $password = $hasPassword && $isFree ? bcrypt($request->password) : null;

            $successCount = 0;

            // Chỉ sử dụng scheduled_publish_at cho chung khi status là draft
            $globalSchedule = ($request->status == 'draft') ? $request->scheduled_publish_at : null;

            foreach ($chapters as $chapter) {
                $slug = 'chuong-' . $chapter['number'] . '-' . Str::slug(Str::limit($chapter['title'], 100));

                // Kiểm tra từng chương có lịch riêng không
                $scheduleDate = null;
                $chapterStatus = $request->status;

                // Nếu có lịch riêng, chương sẽ tự động chuyển sang draft dù status chung là published
                if (isset($chapterSchedules[$chapter['number']]) && !empty($chapterSchedules[$chapter['number']])) {
                    $scheduleDate = $chapterSchedules[$chapter['number']];
                    $chapterStatus = 'draft'; // Bắt buộc phải là draft nếu có lịch
                } elseif ($request->status == 'draft') {
                    // Nếu không có lịch riêng và status chung là draft, dùng lịch chung
                    $scheduleDate = $globalSchedule;
                }

                $story->chapters()->create([
                    'slug' => $slug,
                    'title' => $chapter['title'],
                    'content' => $chapter['content'],
                    'number' => $chapter['number'],
                    'status' => $chapterStatus,
                    'user_id' => $userId,
                    'updated_content_at' => now(),
                    'is_free' => $isFree,
                    'price' => $isFree ? null : $request->price,
                    'password' => $password,
                    'scheduled_publish_at' => $scheduleDate,
                ]);

                $existingNumbers[] = $chapter['number'];
                $existingSlugs[] = $slug;
                $successCount++;
            }

            DB::commit();
            return redirect()->route('user.author.stories.chapters', $story->id)
                ->with('success', "Đã tạo thành công {$successCount} chương mới.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error creating batch chapters: ' . $e->getMessage());
            return back()->with('error', 'Lỗi khi lưu chương')->withInput();
        }
    }

    // Hiển thị form chỉnh sửa chương
    public function editChapter(Story $story, $chapterId)
    {
        if ($story->user_id != Auth::id()) {
            return redirect()->route('user.author.index')
                ->with('error', 'Bạn không có quyền chỉnh sửa chương của truyện này.');
        }

        $chapter = $story->chapters()->findOrFail($chapterId);

        // Lấy chương trước
        $prevChapter = $story->chapters()
            ->where('id', '<', $chapter->id)
            ->orderByDesc('id')
            ->first();

        // Lấy chương sau
        $nextChapter = $story->chapters()
            ->where('id', '>', $chapter->id)
            ->orderBy('id')
            ->first();

        return view('pages.information.author.author_chapter_edit', compact('story', 'chapter', 'prevChapter', 'nextChapter'));
    }


    // Xử lý cập nhật chương
    public function updateChapter(Request $request, Story $story, $chapterId)
    {
        // Kiểm tra nếu truyện không thuộc về người dùng hiện tại
        if ($story->user_id != Auth::id()) {
            return redirect()->route('user.author.index')
                ->with('error', 'Bạn không có quyền chỉnh sửa chương của truyện này.');
        }

        $chapter = $story->chapters()->findOrFail($chapterId);

        $request->validate([
            'title' => 'nullable|max:255|unique:chapters,title,' . $chapter->id . ',id,story_id,' . $story->id,
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
            'is_free' => 'required|boolean',
            'price' => 'required_if:is_free,0|nullable|integer|min:1',
            // Thay đổi validation rule cho mật khẩu
            'password' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request, $chapter) {
                    if ($request->is_free && $request->has_password == 1 && empty($chapter->password) && empty($value)) {
                        $fail('Vui lòng nhập mật khẩu cho chương');
                    }
                },
                'string',
                'max:50'
            ],
            'has_password' => 'required_if:is_free,1|boolean',
            'scheduled_publish_at' => 'nullable|date|after:now',
            'status' => 'required|in:draft,published',
        ], [
            'title.required' => 'Tên chương không được để trống',
            'title.max' => 'Tên chương không được quá 255 ký tự',
            'title.unique' => 'Tên chương này đã tồn tại trong truyện',
            'content.required' => 'Nội dung chương không được để trống',
            'number.required' => 'Số chương không được để trống',
            'number.integer' => 'Số chương phải là số nguyên',
            'is_free.required' => 'Vui lòng chọn hình thức chương',
            'price.required_if' => 'Vui lòng nhập giá xu cho chương',
            'price.integer' => 'Giá xu phải là số nguyên',
            'price.min' => 'Giá xu phải lớn hơn 0',
            'password.required_if' => 'Vui lòng nhập mật khẩu cho chương',
            'scheduled_publish_at.date' => 'Thời gian hẹn giờ không hợp lệ',
            'scheduled_publish_at.after' => 'Thời gian hẹn giờ phải sau thời điểm hiện tại',
            'status.required' => 'Vui lòng chọn trạng thái chương',
        ]);

        try {
            // Tạo slug dự kiến để kiểm tra trùng lặp
            $proposedSlug = 'chuong-' . $request->number . '-' . Str::slug(Str::limit($request->title, 100));

            // Kiểm tra xem slug đã tồn tại chưa nhưng không tính chương hiện tại
            if ($story->chapters()->where('slug', $proposedSlug)->where('id', '!=', $chapter->id)->exists()) {
                return redirect()->back()
                    ->with('error', 'Tiêu đề chương này tạo ra slug đã tồn tại. Vui lòng sử dụng tiêu đề khác.')
                    ->withInput();
            }

            $passwordUpdate = ($request->is_free && $request->has_password);
            $password = null;

            if ($passwordUpdate) {
                // Nếu mật khẩu được nhập, mã hóa mật khẩu mới
                if (!empty($request->password)) {
                    $password = bcrypt($request->password);
                }
                // Nếu không nhập mật khẩu mới, giữ lại mật khẩu cũ
                else if (!empty($chapter->password)) {
                    $password = $chapter->password;
                }
            }

            // Chỉ sử dụng scheduled_publish_at khi status là draft
            $scheduledPublishAt = null;
            if ($request->status == 'draft' && $request->has('scheduled_publish_at')) {
                $scheduledPublishAt = $request->scheduled_publish_at;
            }

            $chapter->update([
                'slug' => $proposedSlug,
                'title' => $request->title ?: 'Chương ' . $request->number,
                'content' => $request->content,
                'number' => $request->number,
                'status' => $request->status,
                'updated_content_at' => now(),
                'is_free' => $request->is_free,
                'price' => !$request->is_free ? $request->price : null,
                'password' => $password,
                'scheduled_publish_at' => $scheduledPublishAt,
            ]);

            return redirect()->back()
                ->with('success', 'Cập nhật chương ' . $request->number . ' thành công');
        } catch (\Exception $e) {
            Log::error('Error updating chapter: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi cập nhật chương. Vui lòng thử lại.')
                ->withInput();
        }
    }

    // Xử lý xóa chương
    public function destroyChapter(Story $story, $chapterId)
    {
        // Kiểm tra nếu truyện không thuộc về người dùng hiện tại
        if ($story->user_id != Auth::id()) {
            return redirect()->route('user.author.index')
                ->with('error', 'Bạn không có quyền xóa chương của truyện này.');
        }

        $chapter = $story->chapters()->findOrFail($chapterId);

        try {
            $chapter->delete();
            return redirect()->route('user.author.stories.chapters', $story->id)
                ->with('success', 'Xóa chương thành công');
        } catch (\Exception $e) {
            Log::error('Error deleting chapter: ' . $e->getMessage());
            return redirect()->route('user.author.stories.chapters', $story->id)
                ->with('error', 'Có lỗi xảy ra khi xóa chương. Vui lòng thử lại.');
        }
    }

    // Thêm phương thức mới để gửi yêu cầu duyệt
    public function submitForReview(Request $request, Story $story)
    {
        // Kiểm tra nếu truyện không thuộc về người dùng hiện tại
        if ($story->user_id != Auth::id()) {
            return redirect()->route('user.author.index')
                ->with('error', 'Bạn không có quyền thực hiện hành động này.');
        }

        // Kiểm tra số lượng chương
        $chapterCount = $story->chapters()->count();
        if ($chapterCount < 3) {
            return redirect()->route('user.author.stories.edit', $story->id)
                ->with('error', 'Truyện phải có ít nhất 3 chương trước khi gửi yêu cầu duyệt.');
        }

        // Validate dữ liệu
        $request->validate([
            'review_note' => 'nullable|max:500',
        ], [
            'review_note.max' => 'Ghi chú không được quá 500 ký tự.',
        ]);

        try {
            $story->update([
                'status' => 'pending',
                'review_note' => $request->review_note,
                'submitted_at' => now(),
            ]);

            return redirect()->route('user.author.stories.edit', $story->id)
                ->with('success', 'Truyện đã được gửi đi và đang chờ phê duyệt.');
        } catch (\Exception $e) {
            Log::error('Error submitting story for review: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi gửi yêu cầu duyệt. Vui lòng thử lại.');
        }
    }

    // Thêm phương thức để kiểm tra tình trạng truyện (dùng cho API hoặc AJAX)
    public function checkStoryStatus(Story $story)
    {
        // Kiểm tra nếu truyện không thuộc về người dùng hiện tại
        if ($story->user_id != Auth::id()) {
            return response()->json([
                'error' => 'Bạn không có quyền truy cập truyện này.'
            ], 403);
        }

        $chapterCount = $story->chapters()->count();
        $canSubmit = $chapterCount >= 3 && $story->status === 'draft';

        return response()->json([
            'status' => $story->status,
            'chapter_count' => $chapterCount,
            'can_submit' => $canSubmit,
            'approved' => $story->approved,
        ]);
    }

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

    // Hiển thị trang doanh thu
    public function revenue()
    {
        // Lấy năm hiện tại và năm trước đó để hiển thị trong dropdown
        $currentYear = date('Y');
        $years = range($currentYear, $currentYear - 5);

        // Lấy thống kê tổng quát
        $totalRevenue = DB::table('chapter_purchases')
            ->join('chapters', 'chapter_purchases.chapter_id', '=', 'chapters.id')
            ->join('stories', 'chapters.story_id', '=', 'stories.id')
            ->where('stories.user_id', Auth::id())
            ->sum('chapter_purchases.amount_received');

        $totalStoryRevenue = DB::table('story_purchases')
            ->join('stories', 'story_purchases.story_id', '=', 'stories.id')
            ->where('stories.user_id', Auth::id())
            ->sum('story_purchases.amount_received');

        // Tính tổng số tiền
        $grandTotal = $totalRevenue + $totalStoryRevenue;

        // Lấy doanh thu của tháng trước để so sánh
        $lastMonthDate = \Carbon\Carbon::now()->subMonth();
        $lastMonthRevenue = DB::table('chapter_purchases')
            ->join('chapters', 'chapter_purchases.chapter_id', '=', 'chapters.id')
            ->join('stories', 'chapters.story_id', '=', 'stories.id')
            ->where('stories.user_id', Auth::id())
            ->whereMonth('chapter_purchases.created_at', $lastMonthDate->month)
            ->whereYear('chapter_purchases.created_at', $lastMonthDate->year)
            ->sum('chapter_purchases.amount_received');

        // Cộng thêm doanh thu từ việc bán trọn bộ trong tháng trước
        $lastMonthRevenue += DB::table('story_purchases')
            ->join('stories', 'story_purchases.story_id', '=', 'stories.id')
            ->where('stories.user_id', Auth::id())
            ->whereMonth('story_purchases.created_at', $lastMonthDate->month)
            ->whereYear('story_purchases.created_at', $lastMonthDate->year)
            ->sum('story_purchases.amount_received');

        // Tính tỷ lệ thay đổi
        $revenueChange = 0;
        $revenueChangePercent = 0;
        $revenueIncreased = false;

        if ($lastMonthRevenue > 0) {
            $revenueChange = $totalRevenue - $lastMonthRevenue;
            $revenueChangePercent = round(($revenueChange / $lastMonthRevenue) * 100);
            $revenueIncreased = $revenueChange > 0;
        } elseif ($totalRevenue > 0) {
            // Nếu tháng trước không có doanh thu nhưng tháng này có
            $revenueChangePercent = 100;
            $revenueIncreased = true;
        }

        // Lấy truyện bán chạy nhất (có nhiều lượt mua nhất)
        $topStories = DB::table('stories')
            ->select('stories.id', 'stories.title', 'stories.slug', DB::raw('COUNT(story_purchases.id) as purchase_count'), DB::raw('SUM(story_purchases.amount_received) as total_revenue'))
            ->leftJoin('story_purchases', 'stories.id', '=', 'story_purchases.story_id')
            ->where('stories.user_id', Auth::id())
            ->groupBy('stories.id', 'stories.title', 'stories.slug')
            ->orderBy('purchase_count', 'desc')
            ->orderBy('total_revenue', 'desc')
            ->limit(5)
            ->get();

        // Lấy chương bán chạy nhất
        $topChapters = DB::table('chapters')
            ->select('chapters.id', 'chapters.title', 'chapters.slug', 'stories.title as story_title', 'stories.slug as story_slug', DB::raw('COUNT(chapter_purchases.id) as purchase_count'), DB::raw('SUM(chapter_purchases.amount_received) as total_revenue'))
            ->join('stories', 'chapters.story_id', '=', 'stories.id')
            ->leftJoin('chapter_purchases', 'chapters.id', '=', 'chapter_purchases.chapter_id')
            ->where('stories.user_id', Auth::id())
            ->groupBy('chapters.id', 'chapters.title', 'chapters.slug', 'stories.title', 'stories.slug')
            ->orderBy('purchase_count', 'desc')
            ->orderBy('total_revenue', 'desc')
            ->limit(5)
            ->get();

        return view('pages.information.author.author_revenue', compact(
            'years',
            'grandTotal',
            'topStories',
            'topChapters',
            'lastMonthRevenue',
            'revenueChangePercent',
            'revenueIncreased'
        ));
    }

    // API endpoint để lấy dữ liệu doanh thu
    public function getRevenueData(Request $request)
    {
        $request->validate([
            'year' => 'nullable|integer|min:2000|max:2100',
            'month' => 'nullable|integer|min:1|max:12',
        ]);

        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m'));

        // Nếu chỉ có year mà không có month, lấy dữ liệu theo từng tháng của năm đó
        if ($request->has('year') && !$request->has('month')) {
            return $this->getYearlyRevenueData($year);
        }

        // Nếu có cả year và month, lấy dữ liệu theo từng ngày của tháng đó
        return $this->getMonthlyRevenueData($year, $month);
    }

    // Hàm lấy dữ liệu doanh thu theo năm, chia theo tháng
    private function getYearlyRevenueData($year)
    {
        // Lấy doanh thu từ việc mua chương
        $chapterRevenue = DB::table('chapter_purchases')
            ->join('chapters', 'chapter_purchases.chapter_id', '=', 'chapters.id')
            ->join('stories', 'chapters.story_id', '=', 'stories.id')
            ->where('stories.user_id', Auth::id())
            ->whereYear('chapter_purchases.created_at', $year)
            ->selectRaw('MONTH(chapter_purchases.created_at) as month, SUM(chapter_purchases.amount_received) as total')
            ->groupBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();

        // Lấy doanh thu từ việc mua trọn bộ
        $storyRevenue = DB::table('story_purchases')
            ->join('stories', 'story_purchases.story_id', '=', 'stories.id')
            ->where('stories.user_id', Auth::id())
            ->whereYear('story_purchases.created_at', $year)
            ->selectRaw('MONTH(story_purchases.created_at) as month, SUM(story_purchases.amount_received) as total')
            ->groupBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();

        // Khởi tạo mảng dữ liệu cho 12 tháng
        $data = [];
        $labels = [];
        $chapterData = [];
        $storyData = [];
        $totalData = [];

        // Tính tổng doanh thu
        $totalChapterRevenue = 0;
        $totalStoryRevenue = 0;

        for ($month = 1; $month <= 12; $month++) {
            $labels[] = 'Tháng ' . $month;

            $chapterAmount = $chapterRevenue[$month] ?? 0;
            $storyAmount = $storyRevenue[$month] ?? 0;
            $totalAmount = $chapterAmount + $storyAmount;

            $chapterData[] = $chapterAmount;
            $storyData[] = $storyAmount;
            $totalData[] = $totalAmount;

            $totalChapterRevenue += $chapterAmount;
            $totalStoryRevenue += $storyAmount;
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Doanh thu từ chương',
                    'data' => $chapterData,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Doanh thu từ trọn bộ',
                    'data' => $storyData,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Tổng doanh thu',
                    'data' => $totalData,
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderWidth' => 1
                ]
            ],
            'summary' => [
                'totalChapterRevenue' => $totalChapterRevenue,
                'totalStoryRevenue' => $totalStoryRevenue,
                'totalRevenue' => $totalChapterRevenue + $totalStoryRevenue
            ]
        ]);
    }

    // Hàm lấy dữ liệu doanh thu theo tháng, chia theo ngày
    private function getMonthlyRevenueData($year, $month)
    {
        // Lấy số ngày trong tháng
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        // Lấy doanh thu từ việc mua chương
        $chapterRevenue = DB::table('chapter_purchases')
            ->join('chapters', 'chapter_purchases.chapter_id', '=', 'chapters.id')
            ->join('stories', 'chapters.story_id', '=', 'stories.id')
            ->where('stories.user_id', Auth::id())
            ->whereYear('chapter_purchases.created_at', $year)
            ->whereMonth('chapter_purchases.created_at', $month)
            ->selectRaw('DAY(chapter_purchases.created_at) as day, SUM(chapter_purchases.amount_received) as total')
            ->groupBy('day')
            ->get()
            ->pluck('total', 'day')
            ->toArray();

        // Lấy doanh thu từ việc mua trọn bộ
        $storyRevenue = DB::table('story_purchases')
            ->join('stories', 'story_purchases.story_id', '=', 'stories.id')
            ->where('stories.user_id', Auth::id())
            ->whereYear('story_purchases.created_at', $year)
            ->whereMonth('story_purchases.created_at', $month)
            ->selectRaw('DAY(story_purchases.created_at) as day, SUM(story_purchases.amount_received) as total')
            ->groupBy('day')
            ->get()
            ->pluck('total', 'day')
            ->toArray();

        // Khởi tạo mảng dữ liệu cho các ngày trong tháng
        $data = [];
        $labels = [];
        $chapterData = [];
        $storyData = [];
        $totalData = [];

        // Tính tổng doanh thu
        $totalChapterRevenue = 0;
        $totalStoryRevenue = 0;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $labels[] = $day;

            $chapterAmount = $chapterRevenue[$day] ?? 0;
            $storyAmount = $storyRevenue[$day] ?? 0;
            $totalAmount = $chapterAmount + $storyAmount;

            $chapterData[] = $chapterAmount;
            $storyData[] = $storyAmount;
            $totalData[] = $totalAmount;

            $totalChapterRevenue += $chapterAmount;
            $totalStoryRevenue += $storyAmount;
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Doanh thu từ chương',
                    'data' => $chapterData,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Doanh thu từ trọn bộ',
                    'data' => $storyData,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Tổng doanh thu',
                    'data' => $totalData,
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderWidth' => 1
                ]
            ],
            'summary' => [
                'totalChapterRevenue' => $totalChapterRevenue,
                'totalStoryRevenue' => $totalStoryRevenue,
                'totalRevenue' => $totalChapterRevenue + $totalStoryRevenue
            ]
        ]);
    }

    /**
     * API để lấy lịch sử giao dịch theo năm và tháng
     */
    public function getTransactionHistory(Request $request)
    {
        $request->validate([
            'year' => 'nullable|integer|min:2000|max:2100',
            'month' => 'nullable|integer|min:1|max:12',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:5|max:50',
        ]);

        $year = $request->input('year', date('Y'));
        $month = $request->input('month');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);

        // Query cho chapter purchases
        $chapterPurchasesQuery = DB::table('chapter_purchases')
            ->select(
                'chapter_purchases.id',
                'chapter_purchases.created_at',
                'chapter_purchases.amount_received',
                'chapters.title as chapter_title',
                'chapters.slug as chapter_slug',
                'chapters.number as chapter_number',
                'stories.title as story_title',
                'stories.slug as story_slug',
                'users.name as user_name',
                DB::raw("'chapter' as type")
            )
            ->join('chapters', 'chapter_purchases.chapter_id', '=', 'chapters.id')
            ->join('stories', 'chapters.story_id', '=', 'stories.id')
            ->join('users', 'chapter_purchases.user_id', '=', 'users.id')
            ->where('stories.user_id', Auth::id())
            ->whereYear('chapter_purchases.created_at', $year);

        // Nếu có tháng, thêm điều kiện lọc theo tháng
        if ($month) {
            $chapterPurchasesQuery->whereMonth('chapter_purchases.created_at', $month);
        }

        // Query cho story purchases
        $storyPurchasesQuery = DB::table('story_purchases')
            ->select(
                'story_purchases.id',
                'story_purchases.created_at',
                'story_purchases.amount_received',
                DB::raw("'' as chapter_title"),
                DB::raw("'' as chapter_slug"),
                DB::raw("0 as chapter_number"),
                'stories.title as story_title',
                'stories.slug as story_slug',
                'users.name as user_name',
                DB::raw("'story' as type")
            )
            ->join('stories', 'story_purchases.story_id', '=', 'stories.id')
            ->join('users', 'story_purchases.user_id', '=', 'users.id')
            ->where('stories.user_id', Auth::id())
            ->whereYear('story_purchases.created_at', $year);

        // Nếu có tháng, thêm điều kiện lọc theo tháng
        if ($month) {
            $storyPurchasesQuery->whereMonth('story_purchases.created_at', $month);
        }

        // Kết hợp hai query và phân trang
        $transactions = $chapterPurchasesQuery->union($storyPurchasesQuery)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json($transactions);
    }

    /**
     * API để lấy danh sách truyện bán chạy nhất với phân trang
     */
    public function getTopStories(Request $request)
    {
        $request->validate([
            'year' => 'nullable|integer|min:2000|max:2100',
            'month' => 'nullable|integer|min:1|max:12',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:5|max:50',
        ]);

        $year = $request->input('year', date('Y'));
        $month = $request->input('month');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);

        // Query lấy truyện bán chạy nhất
        $query = DB::table('stories')
            ->select('stories.id', 'stories.title', 'stories.slug', DB::raw('COUNT(story_purchases.id) as purchase_count'), DB::raw('SUM(story_purchases.amount_received) as total_revenue'))
            ->leftJoin('story_purchases', 'stories.id', '=', 'story_purchases.story_id')
            ->where('stories.user_id', Auth::id())
            ->whereYear('story_purchases.created_at', $year);

        // Nếu có tháng, thêm điều kiện lọc theo tháng
        if ($month) {
            $query->whereMonth('story_purchases.created_at', $month);
        }

        $topStories = $query->groupBy('stories.id', 'stories.title', 'stories.slug')
            ->orderBy('purchase_count', 'desc')
            ->orderBy('total_revenue', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json($topStories);
    }

    /**
     * API để lấy danh sách chương bán chạy nhất với phân trang
     */
    public function getTopChapters(Request $request)
    {
        $request->validate([
            'year' => 'nullable|integer|min:2000|max:2100',
            'month' => 'nullable|integer|min:1|max:12',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:5|max:50',
        ]);

        $year = $request->input('year', date('Y'));
        $month = $request->input('month');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);

        // Query lấy chương bán chạy nhất
        $query = DB::table('chapters')
            ->select('chapters.id', 'chapters.title', 'chapters.slug', 'chapters.number', 'stories.title as story_title', 'stories.slug as story_slug', DB::raw('COUNT(chapter_purchases.id) as purchase_count'), DB::raw('SUM(chapter_purchases.amount_received) as total_revenue'))
            ->join('stories', 'chapters.story_id', '=', 'stories.id')
            ->leftJoin('chapter_purchases', 'chapters.id', '=', 'chapter_purchases.chapter_id')
            ->where('stories.user_id', Auth::id())
            ->whereYear('chapter_purchases.created_at', $year);

        // Nếu có tháng, thêm điều kiện lọc theo tháng
        if ($month) {
            $query->whereMonth('chapter_purchases.created_at', $month);
        }

        $topChapters = $query->groupBy('chapters.id', 'chapters.title', 'chapters.slug', 'chapters.number', 'stories.title', 'stories.slug')
            ->orderBy('purchase_count', 'desc')
            ->orderBy('total_revenue', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json($topChapters);
    }

    public function checkDuplicates(Request $request, Story $story)
    {
        if ($story->user_id != Auth::id()) {
            return response()->json([
                'error' => 'Bạn không có quyền truy cập truyện này.'
            ], 403);
        }

        $chapterNumbers = $request->input('chapter_numbers', []);
        $chapterTitles = $request->input('chapter_titles', []);

        // Check for duplicate chapter numbers
        $duplicateNumbers = Chapter::where('story_id', $story->id)
            ->whereIn('number', $chapterNumbers)
            ->pluck('number')
            ->toArray();

        // Check for duplicate titles (by slug)
        $duplicateTitles = [];
        foreach ($chapterTitles as $title) {
            $slug = Str::slug($title);
            $exists = Chapter::where('story_id', $story->id)
                ->where('slug', $slug)
                ->exists();

            if ($exists) {
                $duplicateTitles[] = $title;
            }
        }

        return response()->json([
            'duplicate_numbers' => $duplicateNumbers,
            'duplicate_titles' => $duplicateTitles
        ]);
    }

    public function bulkPriceForm(Story $story)
    {
        if ($story->user_id != Auth::id()) {
            return response()->json([
                'error' => 'Bạn không có quyền truy cập truyện này.'
            ], 403);
        }

        $chapters = $story->chapters()
            ->where('status', 'published')
            ->select('id', 'title', 'number', 'is_free', 'price','password')
            ->orderBy('number')
            ->get();

        return view('pages.information.author.author_chapters_bulk_price', compact('story', 'chapters'));
    }

    /**
     * Update chapter prices in bulk
     */
    public function bulkPriceUpdate(Request $request, Story $story)
    {
        if ($story->user_id != Auth::id()) {
            return redirect()->route('user.author.stories.chapters', $story->id)
                ->with('error', 'Bạn không có quyền truy cập truyện này.');
        }

        $request->validate([
            'update_type' => 'required|in:all_same,individual',
            'all_price' => 'required_if:update_type,all_same|nullable|numeric|min:0',
            'chapter_prices' => 'required_if:update_type,individual|array',
            'chapter_prices.*' => 'nullable|numeric|min:0',
            'selected_chapters' => 'required|array|min:1',
            'selected_chapters.*' => 'exists:chapters,id',
        ], [
            'update_type.required' => 'Vui lòng chọn loại cập nhật.',
            'all_price.required_if' => 'Vui lòng nhập giá áp dụng cho tất cả.',
            'all_price.min' => 'Giá tối thiểu là 0 xu (miễn phí).',
            'chapter_prices.required_if' => 'Vui lòng nhập giá cho từng chương.',
            'chapter_prices.*.min' => 'Giá tối thiểu là 0 xu (miễn phí).',
            'selected_chapters.required' => 'Vui lòng chọn ít nhất một chương.',
            'selected_chapters.min' => 'Vui lòng chọn ít nhất một chương.',
        ]);

        $selectedChapterIds = $request->selected_chapters;

        try {
            DB::beginTransaction();

            if ($request->update_type === 'all_same') {
                // Cập nhật tất cả chapters được chọn với cùng giá
                $allPrice = $request->all_price;
                $isFree = empty($allPrice) || $allPrice == 0;

                $updateData = [
                    'is_free' => $isFree,
                    'price' => $isFree ? null : $allPrice,
                ];

                $updatedCount = Chapter::whereIn('id', $selectedChapterIds)
                    ->where('story_id', $story->id)
                    ->where('status', 'published')
                    ->where(function ($query) use ($isFree, $allPrice) {
                        // Chỉ update những chapter có thay đổi
                        $query->where('is_free', '!=', $isFree)
                            ->orWhere('price', '!=', $isFree ? null : $allPrice);
                    })
                    ->update($updateData);

                Log::info("Bulk price update - All same", [
                    'story_id' => $story->id,
                    'chapter_ids' => $selectedChapterIds,
                    'new_price' => $allPrice,
                    'is_free' => $isFree,
                    'updated_count' => $updatedCount,
                    'user_id' => auth()->id()
                ]);
            } else {
                // Cập nhật từng chương với giá riêng
                $updatedCount = 0;

                foreach ($selectedChapterIds as $chapterId) {
                    if (array_key_exists($chapterId, $request->chapter_prices)) {
                        $newPrice = $request->chapter_prices[$chapterId];
                        $isFree = empty($newPrice) || $newPrice == 0;

                        $chapter = Chapter::where('id', $chapterId)
                            ->where('story_id', $story->id)
                            ->where('status', 'published')
                            ->first();

                        if ($chapter) {
                            // Kiểm tra xem có thay đổi thực sự không
                            $needsUpdate = false;

                            if ($isFree) {
                                // Nếu set miễn phí: is_free = true, price = null
                                if (!$chapter->is_free || $chapter->price !== null) {
                                    $needsUpdate = true;
                                }
                            } else {
                                // Nếu có giá: is_free = false, price = giá mới
                                if ($chapter->is_free || $chapter->price != $newPrice) {
                                    $needsUpdate = true;
                                }
                            }

                            if ($needsUpdate) {
                                $oldPrice = $chapter->price;
                                $oldIsFree = $chapter->is_free;

                                $chapter->update([
                                    'is_free' => $isFree,
                                    'price' => $isFree ? null : $newPrice,
                                ]);

                                $updatedCount++;

                                Log::info("Individual chapter price updated", [
                                    'story_id' => $story->id,
                                    'chapter_id' => $chapterId,
                                    'chapter_number' => $chapter->number,
                                    'old_price' => $oldPrice,
                                    'old_is_free' => $oldIsFree,
                                    'new_price' => $isFree ? null : $newPrice,
                                    'new_is_free' => $isFree,
                                    'user_id' => auth()->id()
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();

            if ($updatedCount > 0) {
                return redirect()
                    ->back()
                    ->with('success', "Đã cập nhật giá cho {$updatedCount} chương thành công.");
            } else {
                return back()->with('warning', 'Không có chương nào được cập nhật (thông tin mới giống thông tin cũ).');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk price update failed', [
                'story_id' => $story->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return back()->withErrors(['error' => 'Có lỗi xảy ra khi cập nhật giá. Vui lòng thử lại.']);
        }
    }
}
