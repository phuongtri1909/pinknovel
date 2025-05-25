<?php

namespace App\Http\Controllers;

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
use App\Models\StoryEditRequest;

class AuthorController extends Controller
{
    // Hiển thị danh sách truyện của tác giả
    public function index()
    {
        // Dashboard của tác giả - Trang chính
        $stories = auth()->user()->stories()->latest()->paginate(5);
        $pendingCount = auth()->user()->stories()->where('status', 'pending')->count();

        // Thêm các biến thống kê cho dashboard
        $totalViews = auth()->user()->stories()->withCount('chapters')->get()->sum('views');
        $totalChapters = auth()->user()->stories()->withCount('chapters')->get()->sum('chapters_count');
        $totalComments = auth()->user()->stories()->withCount('comments')->get()->sum('comments_count');
        $totalFollowers = Bookmark::where('story_id', 'in', auth()->user()->stories()->pluck('id'))->count();

        // Có thể thêm hoạt động gần đây nếu bạn có bảng dữ liệu tương ứng
        // $recentActivities = Activity::where('user_id', auth()->id())->latest()->take(5)->get();

        return view('pages.information.author.author', compact(
            'stories',
            'pendingCount',
            'totalViews',
            'totalChapters',
            'totalComments',
            'totalFollowers'
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

            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi tạo truyện: ' . $e->getMessage())->withInput();
        }
    }

    // Hiển thị form chỉnh sửa truyện
    public function edit(Story $story)
    {
        // Kiểm tra nếu truyện không thuộc về người dùng hiện tại
        if ($story->user_id !== Auth::id()) {
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
        if ($story->user_id !== Auth::id()) {
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
            // Kiểm tra trạng thái của truyện
            if ($story->status === 'published') {
                // Nếu story đã xuất bản, tạo edit request thay vì cập nhật trực tiếp

                // Kiểm tra xem đã có edit request nào đang chờ duyệt chưa
                if ($story->hasPendingEditRequest()) {
                    return redirect()->back()
                        ->with('error', 'Đã có một yêu cầu chỉnh sửa đang chờ duyệt. Vui lòng đợi admin xét duyệt.')
                        ->withInput();
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
                    $story->is_monopoly !== $request->is_monopoly
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
                        ->with('info', 'Không có thay đổi nào được phát hiện so với thông tin hiện tại.')
                        ->withInput();
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
                    // Xử lý ảnh mới
                    $coverPaths = $this->processAndSaveImage($request->file('cover'));

                    $editRequestData['cover'] = $coverPaths['original'];
                    $editRequestData['cover_medium'] = $coverPaths['medium'];
                    $editRequestData['cover_thumbnail'] = $coverPaths['thumbnail'];
                }

                // Tạo yêu cầu chỉnh sửa mới
                $editRequest = StoryEditRequest::create($editRequestData);

                DB::commit();

                return redirect()->route('user.author.stories.edit', $story->id)
                    ->with('success', 'Yêu cầu chỉnh sửa truyện đã được gửi đi và đang chờ phê duyệt.');
            } else {
                // Nếu story chưa xuất bản (nháp hoặc đang chờ duyệt), cập nhật trực tiếp
                $data = [
                    'title' => $request->title,
                    'slug' => Str::slug($request->title),
                    'description' => $request->description,
                    'author_name' => $request->author_name,
                    'story_type' => $request->story_type,
                    'translator_name' => $request->translator_name,
                    'is_18_plus' => $request->has('is_18_plus'),
                    'is_monopoly' => $request->has('is_monopoly'),
                    'status' => 'draft',
                ];

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

                return redirect()->route('user.author.stories.edit', $story->id)
                    ->with('success', 'Truyện đã được cập nhật');
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

            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi cập nhật truyện: ' . $e->getMessage())->withInput();
        }
    }

    // Xử lý xóa truyện
    public function destroy(Story $story)
    {
        // Kiểm tra nếu truyện không thuộc về người dùng hiện tại
        if ($story->user_id !== Auth::id()) {
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

            return redirect()->route('user.author.stories.index')
                ->with('success', 'Truyện đã được xóa thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('user.author.stories.index')
                ->with('error', 'Có lỗi xảy ra khi xóa truyện: ' . $e->getMessage());
        }
    }

    public function markComplete(Story $story)
    {
        // Kiểm tra nếu truyện không thuộc về người dùng hiện tại
        if ($story->user_id !== Auth::id()) {
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
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // Hiển thị danh sách các chương của truyện
    public function showChapters(Story $story)
    {
        // Kiểm tra nếu truyện không thuộc về người dùng hiện tại
        if ($story->user_id !== Auth::id()) {
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
        if ($story->user_id !== Auth::id()) {
            return redirect()->route('user.author.index')
                ->with('error', 'Bạn không có quyền thêm chương cho truyện này.');
        }

        $latestChapterNumber = $story->chapters()->max('number') ?? 0;
        $nextChapterNumber = $latestChapterNumber + 1;

        return view('pages.information.author.author_chapter_create', compact('story', 'nextChapterNumber'));
    }

    // Xử lý lưu chương mới
    public function storeChapter(Request $request, Story $story)
    {
        // Kiểm tra nếu truyện không thuộc về người dùng hiện tại
        if ($story->user_id !== Auth::id()) {
            return redirect()->route('user.author.index')
                ->with('error', 'Bạn không có quyền thêm chương cho truyện này.');
        }

        // Kiểm tra nếu là nhiều chương
        if ($request->upload_type === 'multiple') {
            return $this->storeBatchChapters($request, $story);
        }

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
            'is_free' => 'required|boolean',
            'price' => 'required_if:is_free,0|nullable|integer|min:1',
            'password' => 'nullable|required_if:has_password,1|string|max:50',
            'has_password' => 'required_if:is_free,1|boolean',
            'scheduled_publish_at' => 'nullable|date|after:now',
            'status' => 'required|in:draft,published',
        ], [
            'title.required' => 'Tên chương không được để trống',
            'content.required' => 'Nội dung chương không được để trống',
            'number.required' => 'Số chương không được để trống',
            'number.integer' => 'Số chương phải là số nguyên',
            'is_free.required' => 'Vui lòng chọn hình thức chương',
            'price.required_if' => 'Vui lòng nhập giá coin cho chương',
            'price.integer' => 'Giá coin phải là số nguyên',
            'price.min' => 'Giá coin phải lớn hơn 0',
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

            $chapter = $story->chapters()->create([
                'slug' => $proposedSlug,
                'title' => $request->title,
                'content' => $request->content,
                'number' => $request->number,
                'status' => $request->status,
                'user_id' => Auth::id(),
                'updated_content_at' => now(),
                'is_free' => $request->is_free,
                'price' => !$request->is_free ? $request->price : null,
                'password' => ($request->is_free && $request->has_password) ? bcrypt($request->password) : null,
                'scheduled_publish_at' => $request->scheduled_publish_at,
            ]);

            return redirect()->route('user.author.stories.chapters', $story->id)
                ->with('success', 'Đã tạo chương ' . $request->number . ' thành công');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Hàm phân tích nội dung batch để tách thành các chương
    private function parseChaptersFromBatchContent($batchContent, $chapterPrefix = '')
    {
        // Xử lý tiền tố mặc định nếu không được cung cấp
        $prefixPattern = empty($chapterPrefix) ? '(Chương|Chapter|Chap)' : preg_quote($chapterPrefix, '/');

        // Hỗ trợ cả hai định dạng:
        // 1. [Chương X] : Tiêu đề (mới)
        // 2. Chương X: Tiêu đề hoặc Chương X - Tiêu đề (cũ)
        $pattern1 = '/^\[' . $prefixPattern . '\s+(\d+)\]\s*:\s*(.+?)$/m'; // Format mới [Chương X] : Tiêu đề
        $pattern2 = '/^' . $prefixPattern . '\s+(\d+)[\s:\-]+(.+?)$/m'; // Format cũ Chương X: Tiêu đề

        // Tìm tất cả các tiêu đề chương theo format mới
        preg_match_all($pattern1, $batchContent, $matches1, PREG_OFFSET_CAPTURE);

        // Tìm tất cả các tiêu đề chương theo format cũ
        preg_match_all($pattern2, $batchContent, $matches2, PREG_OFFSET_CAPTURE);


        // Merge kết quả từ hai pattern
        $matches = [
            array_merge($matches1[0], $matches2[0]), // Full matches
            array_merge($matches1[1], $matches2[1]), // Prefix captures
            array_merge($matches1[2], $matches2[2]), // Chapter numbers
            array_merge($matches1[3], $matches2[3]), // Titles
        ];

        // Sắp xếp lại các match theo vị trí xuất hiện trong văn bản
        $combined = [];
        foreach ($matches[0] as $key => $match) {
            $combined[] = [
                'full' => $match,
                'prefix' => $matches[1][$key],
                'number' => $matches[2][$key],
                'title' => $matches[3][$key],
            ];
        }

        // Sắp xếp theo vị trí
        usort($combined, function ($a, $b) {
            return $a['full'][1] - $b['full'][1];
        });

        // Nếu không tìm thấy match nào, return empty array
        if (empty($combined)) {
            return [];
        }

        $chapters = [];
        $matchCount = count($combined);

        // Xử lý từng chương
        for ($i = 0; $i < $matchCount; $i++) {
            // Lấy vị trí bắt đầu của tiêu đề hiện tại
            $currentTitlePos = $combined[$i]['full'][1];

            // Lấy tiêu đề đầy đủ và số chương
            $fullTitle = $combined[$i]['full'][0];
            $chapterNumber = (int) $combined[$i]['number'][0]; // Chuyển đổi sang số nguyên
            $titleContent = trim($combined[$i]['title'][0]);

            // Xác định vị trí kết thúc của chương hiện tại
            $contentEndPos = ($i < $matchCount - 1) ? $combined[$i + 1]['full'][1] : strlen($batchContent);

            // Tính toán vị trí bắt đầu của nội dung sau tiêu đề
            $contentStartPos = $currentTitlePos + strlen($fullTitle);

            // Lấy nội dung giữa cuối tiêu đề hiện tại và đầu tiêu đề tiếp theo
            $content = substr($batchContent, $contentStartPos, $contentEndPos - $contentStartPos);

            // Làm sạch nội dung
            $content = trim($content);

            // Thêm chương vào mảng kết quả
            $chapters[] = [
                'number' => $chapterNumber,
                'title' => $titleContent,
                'content' => $content
            ];
        }

        return $chapters;
    }

    // Xử lý lưu nhiều chương cùng lúc
    public function storeBatchChapters(Request $request, Story $story)
    {
        $request->validate([
            'batch_content' => 'required',
            'chapter_prefix' => 'nullable|string|max:50',
            'is_free' => 'required|boolean',
            'price' => 'required_if:is_free,0|nullable|integer|min:1',
            'password' => 'nullable|required_if:has_password,1|string|max:50',
            'has_password' => 'required_if:is_free,1|boolean',
            'scheduled_publish_at' => 'nullable|date|after:now',
            'status' => 'required|in:draft,published',
        ], [
            'batch_content.required' => 'Nội dung các chương không được để trống',
            'is_free.required' => 'Vui lòng chọn hình thức chương',
            'price.required_if' => 'Vui lòng nhập giá coin cho chương',
            'price.integer' => 'Giá coin phải là số nguyên',
            'price.min' => 'Giá coin phải lớn hơn 0',
            'password.required_if' => 'Vui lòng nhập mật khẩu cho chương',
            'scheduled_publish_at.date' => 'Thời gian hẹn giờ không hợp lệ',
            'scheduled_publish_at.after' => 'Thời gian hẹn giờ phải sau thời điểm hiện tại',
            'status.required' => 'Vui lòng chọn trạng thái chương',
        ]);

        // Xử lý nội dung batch để tách thành các chương
        $batchContent = $request->batch_content;
        $chapters = $this->parseChaptersFromBatchContent($batchContent, $request->chapter_prefix);

        if (empty($chapters)) {
            return redirect()->back()
                ->with('error', 'Không thể tách nội dung thành các chương. Vui lòng kiểm tra lại định dạng.')
                ->withInput();
        }

        // Lấy danh sách số chương đã tồn tại
        $existingChapterNumbers = $story->chapters()->pluck('number')->toArray();

        // Lấy danh sách slug đã tồn tại 
        $existingSlugs = $story->chapters()->pluck('slug')->toArray();

        $skippedChapters = [];
        $duplicateSlugs = [];
        $successCount = 0;
        $duplicateFound = false;
        
        // Kiểm tra trước để phát hiện các chương trùng lặp
        foreach ($chapters as $chapterData) {
            $chapterNumber = $chapterData['number'];
            $title = $chapterData['title'];
            
            // Tạo slug dự kiến để kiểm tra
            $proposedSlug = 'chuong-' . $chapterNumber . '-' . Str::slug(Str::limit($title, 100));
            
            // Kiểm tra xem chương đã tồn tại chưa
            if (in_array($chapterNumber, $existingChapterNumbers)) {
                $skippedChapters[] = "Chương $chapterNumber"; // Thêm vào danh sách chương bị bỏ qua
                $duplicateFound = true;
            }
            
            // Kiểm tra xem slug đã tồn tại chưa
            if (in_array($proposedSlug, $existingSlugs)) {
                $duplicateSlugs[] = "Chương $chapterNumber ($title)"; // Thêm vào danh sách slug bị trùng
                $duplicateFound = true;
            }
        }
        
        // Nếu phát hiện có chương trùng lặp, thông báo và quay lại form
        if ($duplicateFound) {
            $errorMessage = "Phát hiện chương bị trùng lặp:";
            
            if (!empty($skippedChapters)) {
                $errorMessage .= " Trùng số chương: " . implode(', ', $skippedChapters) . ".";
            }
            
            if (!empty($duplicateSlugs)) {
                $errorMessage .= " Trùng slug (do tiêu đề tương tự): " . implode(', ', $duplicateSlugs) . ".";
            }
            
            $errorMessage .= " Vui lòng chỉnh sửa nội dung và thử lại.";
            
            return redirect()->back()
                ->with('error', $errorMessage)
                ->withInput();
        }

        // Nếu không có trùng lặp, tiến hành lưu các chương
        DB::beginTransaction();
        try {
            foreach ($chapters as $chapterData) {
                $chapterNumber = $chapterData['number'];
                $title = $chapterData['title'];
                $content = $chapterData['content'];

                // Tạo slug
                $proposedSlug = 'chuong-' . $chapterNumber . '-' . Str::slug(Str::limit($title, 100));

                $chapter = $story->chapters()->create([
                    'slug' => $proposedSlug,
                    'title' => $title,
                    'content' => $content,
                    'number' => $chapterNumber,
                    'status' => $request->status,
                    'user_id' => Auth::id(),
                    'updated_content_at' => now(),
                    'is_free' => $request->is_free,
                    'price' => !$request->is_free ? $request->price : null,
                    'password' => ($request->is_free && $request->has_password) ? bcrypt($request->password) : null,
                    'scheduled_publish_at' => $request->scheduled_publish_at,
                ]);

                // Thêm slug mới vào danh sách slug đã tồn tại để tránh trùng lặp trong cùng batch
                $existingSlugs[] = $proposedSlug;
                // Thêm số chương mới vào danh sách đã tồn tại
                $existingChapterNumbers[] = $chapterNumber;

                $successCount++;
            }

            DB::commit();

            return redirect()->route('user.author.stories.chapters', $story->id)
                ->with('success', "Đã tạo thành công {$successCount} chương mới.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Hiển thị form chỉnh sửa chương
    public function editChapter(Story $story, $chapterId)
    {
        // Kiểm tra nếu truyện không thuộc về người dùng hiện tại
        if ($story->user_id !== Auth::id()) {
            return redirect()->route('user.author.index')
                ->with('error', 'Bạn không có quyền chỉnh sửa chương của truyện này.');
        }

        $chapter = $story->chapters()->findOrFail($chapterId);
        return view('pages.information.author.author_chapter_edit', compact('story', 'chapter'));
    }

    // Xử lý cập nhật chương
    public function updateChapter(Request $request, Story $story, $chapterId)
    {
        // Kiểm tra nếu truyện không thuộc về người dùng hiện tại
        if ($story->user_id !== Auth::id()) {
            return redirect()->route('user.author.index')
                ->with('error', 'Bạn không có quyền chỉnh sửa chương của truyện này.');
        }

        $chapter = $story->chapters()->findOrFail($chapterId);

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
            'content.required' => 'Nội dung chương không được để trống',
            'number.required' => 'Số chương không được để trống',
            'number.integer' => 'Số chương phải là số nguyên',
            'is_free.required' => 'Vui lòng chọn hình thức chương',
            'price.required_if' => 'Vui lòng nhập giá coin cho chương',
            'price.integer' => 'Giá coin phải là số nguyên',
            'price.min' => 'Giá coin phải lớn hơn 0',
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
            $chapter->update([
                'slug' => $proposedSlug,
                'title' => $request->title,
                'content' => $request->content,
                'number' => $request->number,
                'status' => $request->status,
                'updated_content_at' => now(),
                'is_free' => $request->is_free,
                'price' => !$request->is_free ? $request->price : null,
                'password' => $password,
                'scheduled_publish_at' => $request->scheduled_publish_at,
            ]);

            return redirect()->route('user.author.stories.chapters', $story->id)
                ->with('success', 'Cập nhật chương ' . $request->number . ' thành công');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Xử lý xóa chương
    public function destroyChapter(Story $story, $chapterId)
    {
        // Kiểm tra nếu truyện không thuộc về người dùng hiện tại
        if ($story->user_id !== Auth::id()) {
            return redirect()->route('user.author.index')
                ->with('error', 'Bạn không có quyền xóa chương của truyện này.');
        }

        $chapter = $story->chapters()->findOrFail($chapterId);

        try {
            $chapter->delete();
            return redirect()->route('user.author.stories.chapters', $story->id)
                ->with('success', 'Xóa chương thành công');
        } catch (\Exception $e) {
            return redirect()->route('user.author.stories.chapters', $story->id)
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // Thêm phương thức mới để gửi yêu cầu duyệt
    public function submitForReview(Request $request, Story $story)
    {
        // Kiểm tra nếu truyện không thuộc về người dùng hiện tại
        if ($story->user_id !== Auth::id()) {
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
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // Thêm phương thức để kiểm tra tình trạng truyện (dùng cho API hoặc AJAX)
    public function checkStoryStatus(Story $story)
    {
        // Kiểm tra nếu truyện không thuộc về người dùng hiện tại
        if ($story->user_id !== Auth::id()) {
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
}
