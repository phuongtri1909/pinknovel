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
    /**
     * Mã hóa mật khẩu chương sử dụng key riêng
     */
    private function encryptChapterPassword($password)
    {
        $key = config('chapter.password_key');
        if (empty($key)) {
            throw new \Exception('CHAPTER_PASSWORD_KEY chưa được cấu hình trong .env');
        }
        
        $cipher = config('chapter.cipher', 'AES-256-CBC');
        $ivLength = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $encrypted = openssl_encrypt($password, $cipher, $key, 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Giải mã mật khẩu chương
     */
    private function decryptChapterPassword($encrypted)
    {
        $key = config('chapter.password_key');
        if (empty($key)) {
            throw new \Exception('CHAPTER_PASSWORD_KEY chưa được cấu hình trong .env');
        }
        
        $cipher = config('chapter.cipher', 'AES-256-CBC');
        
        try {
            $data = base64_decode($encrypted);
            if ($data === false) {
                // Có thể là mật khẩu cũ chưa được encrypt (plain text)
                return $encrypted;
            }
            
            $ivLength = openssl_cipher_iv_length($cipher);
            if (strlen($data) < $ivLength) {
                // Có thể là mật khẩu cũ chưa được encrypt (plain text)
                return $encrypted;
            }
            
            $iv = substr($data, 0, $ivLength);
            $encryptedData = substr($data, $ivLength);
            $decrypted = openssl_decrypt($encryptedData, $cipher, $key, 0, $iv);
            
            if ($decrypted === false) {
                // Giải mã thất bại, có thể là mật khẩu cũ (plain text)
                return $encrypted;
            }
            
            return $decrypted;
        } catch (\Exception $e) {
            // Giải mã lỗi, trả về nguyên bản (có thể là plain text)
            return $encrypted;
        }
    }
    
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

        $stories = $query->with([
            'chapters' => function ($query) {
                $query->select('id', 'story_id', 'views');
            }
        ])
        ->withCount(['chapters', 'purchases as has_story_purchases', 'chapterPurchases as has_chapter_purchases'])
        ->withSum('chapters', 'views')
        ->latest()
        ->paginate(10);

        $storyIds = $stories->pluck('id');
        
        $chapterRevenue = DB::table('chapter_purchases')
            ->join('chapters', 'chapter_purchases.chapter_id', '=', 'chapters.id')
            ->whereIn('chapters.story_id', $storyIds)
            ->select('chapters.story_id', DB::raw('SUM(chapter_purchases.amount_received) as total_revenue'))
            ->groupBy('chapters.story_id')
            ->pluck('total_revenue', 'story_id');

        $storyRevenue = DB::table('story_purchases')
            ->whereIn('story_id', $storyIds)
            ->select('story_id', DB::raw('SUM(amount_received) as total_revenue'))
            ->groupBy('story_id')
            ->pluck('total_revenue', 'story_id');

        $stories->each(function ($story) use ($chapterRevenue, $storyRevenue) {
            $story->total_revenue = ($chapterRevenue->get($story->id, 0) + $storyRevenue->get($story->id, 0));
        });

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
            'source_link' => 'required|url|max:500',
            'is_18_plus' => 'nullable|boolean',
            'is_monopoly' => 'nullable|boolean',
        ], [
            'source_link.required' => 'Link nguồn không được để trống.',
            'source_link.url' => 'Link nguồn không hợp lệ.',
            'source_link.max' => 'Link nguồn không được quá 500 ký tự.',
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

            $storyNotice = $this->processStoryNoticeImages($request->story_notice);
            $story = Story::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'description' => $request->description,
                'story_notice' => $storyNotice,
                'status' => 'draft',
                'cover' => $coverPaths['original'],
                'cover_medium' => $coverPaths['medium'],
                'cover_thumbnail' => $coverPaths['thumbnail'],
                'author_name' => $request->author_name,
                'translator_name' => $request->translator_name,
                'source_link' => $request->source_link,
                'story_type' => $request->story_type,
                'is_18_plus' => $request->has('is_18_plus'),
                'is_monopoly' => $request->has('is_monopoly'),
            ]);

            if ($storyNotice && $story->id) {
                $finalStoryNotice = $this->processStoryNoticeImages($storyNotice, $story->id);
                if ($finalStoryNotice !== $storyNotice) {
                    $story->update(['story_notice' => $finalStoryNotice]);
                }
            }

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
        
        $configs = \App\Models\Config::getConfigs([
            'story_featured_price' => 99999,
            'story_featured_duration' => 1,
        ]);
        
        $featuredPrice = $configs['story_featured_price'];
        $featuredDuration = $configs['story_featured_duration'];
        
        $hasPendingEditRequest = $story->hasPendingEditRequest();
        $latestPendingEditRequest = $hasPendingEditRequest ? $story->latestPendingEditRequest() : null;
        
        
        return view('pages.information.author.author_edit', compact(
            'story', 
            'categoryNames', 
            'featuredPrice', 
            'featuredDuration',
            'hasPendingEditRequest',
            'latestPendingEditRequest'
        ));
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
            'source_link' => 'required|url|max:500',
            'is_monopoly' => 'nullable|boolean',
        ], [
            'source_link.required' => 'Link nguồn không được để trống.',
            'source_link.url' => 'Link nguồn không hợp lệ.',
            'source_link.max' => 'Link nguồn không được quá 500 ký tự.',
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
            if ($story->status === 'published') {

                // Kiểm tra xem đã có edit request nào đang chờ duyệt chưa
                if ($story->hasPendingEditRequest()) {
                    return redirect()->back()
                        ->with('error', 'Truyện này đã có yêu cầu chỉnh sửa đang chờ duyệt. Vui lòng chờ admin xử lý trước khi gửi yêu cầu mới.');
                }

                $hasChanges = false;

                if (
                    $story->title !== $request->title ||
                    $story->description !== $request->description ||
                    $story->story_notice !== $request->story_notice ||
                    $story->author_name !== $request->author_name ||
                    $story->translator_name !== $request->translator_name ||
                    $story->source_link !== $request->source_link ||
                    $story->story_type !== $request->story_type ||
                    $story->is_18_plus !== $request->has('is_18_plus') ||
                    $story->is_monopoly !== $request->has('is_monopoly')
                ) {
                    $hasChanges = true;
                }

                if ($request->hasFile('cover')) {
                    $hasChanges = true;
                }

                $currentCategoryIds = $story->categories->pluck('id')->toArray();
                sort($currentCategoryIds);
                sort($categoryIds);
                if ($currentCategoryIds != $categoryIds) {
                    $hasChanges = true;
                }

                if (!$hasChanges) {
                    return redirect()->back()
                        ->with('info', 'Không có thay đổi nào được phát hiện.');
                }

                $storyNotice = $this->processStoryNoticeImages($request->story_notice, $story->id);

                $editRequestData = [
                    'story_id' => $story->id,
                    'user_id' => Auth::id(),
                    'title' => $request->title,
                    'slug' => Str::slug($request->title),
                    'description' => $request->description,
                    'story_notice' => $storyNotice,
                    'author_name' => $request->author_name,
                    'story_type' => $request->story_type,
                    'is_18_plus' => $request->has('is_18_plus'),
                    'translator_name' => $request->translator_name,
                    'source_link' => $request->source_link,
                    'categories_data' => json_encode($categoryData),
                    'status' => 'pending',
                    'submitted_at' => now(),
                    'is_monopoly' => $request->has('is_monopoly'),
                ];

                if ($request->hasFile('cover')) {
                    $coverPaths = $this->processAndSaveImage($request->file('cover'));

                    $editRequestData['cover'] = $coverPaths['original'];
                    $editRequestData['cover_medium'] = $coverPaths['medium'];
                    $editRequestData['cover_thumbnail'] = $coverPaths['thumbnail'];
                }

                $editRequest = StoryEditRequest::create($editRequestData);

                DB::commit();

                return redirect()->route('user.author.stories.edit', $story->id)
                    ->with('success', 'Yêu cầu chỉnh sửa đã được gửi đi và đang chờ admin phê duyệt.');
            } else {
                $oldStoryNotice = $story->story_notice;
                $oldImages = $this->extractStoryNoticeImages($oldStoryNotice);
                if (!$request->has('story_notice')) {
                    $storyNotice = $oldStoryNotice;
                    $newImages = $oldImages;
                } else {
                    $storyNotice = $this->processStoryNoticeImages($request->story_notice, $story->id);
                    $newImages = $this->extractStoryNoticeImages($storyNotice);
                }

                $data = [
                    'title' => $request->title,
                    'slug' => Str::slug($request->title),
                    'description' => $request->description,
                    'story_notice' => $storyNotice,
                    'author_name' => $request->author_name,
                    'story_type' => $request->story_type,
                    'translator_name' => $request->translator_name,
                    'source_link' => $request->source_link,
                    'is_18_plus' => $request->has('is_18_plus'),
                    'is_monopoly' => $request->has('is_monopoly'),
                ];

                if ($story->status === 'pending') {
                    $data['status'] = 'draft';
                }

                if ($request->hasFile('cover')) {
                    $oldImages = [
                        $story->cover,
                        $story->cover_medium,
                        $story->cover_thumbnail
                    ];

                    $coverPaths = $this->processAndSaveImage($request->file('cover'));

                    $data['cover'] = $coverPaths['original'];
                    $data['cover_jpeg'] = $coverPaths['original_jpeg'];
                    $data['cover_medium'] = $coverPaths['medium'];
                    $data['cover_thumbnail'] = $coverPaths['thumbnail'];
                }

                $story->update($data);
                $story->categories()->sync($categoryIds);

                DB::commit();

                $this->deleteUnusedStoryNoticeImages($oldImages, $newImages);

                if (isset($oldImages) && isset($coverPaths)) {
                    Storage::disk('public')->delete($oldImages);
                }

                $message = 'Truyện đã được cập nhật thành công.';

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
        if ($story->user_id != Auth::id()) {
            return redirect()->route('user.author.index')
                ->with('error', 'Bạn không có quyền xóa truyện này.');
        }

        $hasStoryPurchases = $story->purchases()->exists();
        $hasChapterPurchases = $story->chapterPurchases()->exists();
        
        if ($hasStoryPurchases || $hasChapterPurchases) {
            return redirect()->route('user.author.stories')
                ->with('error', 'Không thể xóa truyện này vì đã có người mua VIP. Vui lòng liên hệ admin nếu cần hỗ trợ.');
        }

        DB::beginTransaction();
        try {
            $story->categories()->detach();
            $story->chapters()->delete();

            $story->delete();

            DB::commit();

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
        if ($story->user_id != Auth::id()) {
            return redirect()->route('user.author.index')
                ->with('error', 'Bạn không có quyền thực hiện hành động này.');
        }

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

    public function showChapters(Story $story)
    {
        if ($story->user_id != Auth::id()) {
            return redirect()->route('user.author.index')
                ->with('error', 'Bạn không có quyền xem các chương của truyện này.');
        }

        // Check if story has been purchased (combo purchase)
        $storyHasPurchases = $story->purchases()->exists();

        $chapters = $story->chapters()
            ->select([
                'id',
                'story_id',
                'number',
                'title',
                'slug',
                'status',
                'is_free',
                'price',
                'password',
                'views',
                'created_at',
                'updated_at',
                'scheduled_publish_at'
            ])
            ->withCount('purchases')
            ->orderBy('number', 'desc')
            ->paginate(20);

        return view('pages.information.author.author_chapters', compact('story', 'chapters', 'storyHasPurchases'));
    }

    public function createChapter(Story $story)
    {
        if ($story->user_id != Auth::id()) {
            return redirect()->route('user.author.index')
                ->with('error', 'Bạn không có quyền thêm chương cho truyện này.');
        }

        $latestChapterNumber = $story->chapters()->max('number') ?? 0;
        $nextChapterNumber = $latestChapterNumber + 1;

        return view('pages.information.author.author_chapter_create', compact('story', 'nextChapterNumber'));
    }

    public function createBatchChapters(Story $story)
    {
        if ($story->user_id != Auth::id()) {
            return redirect()->route('user.author.index')
                ->with('error', 'Bạn không có quyền thêm chương cho truyện này.');
        }

        $latestChapterNumber = $story->chapters()->max('number') ?? 0;
        $nextChapterNumber = $latestChapterNumber + 1;

        return view('pages.information.author.author_batch_chapter_create', compact('story', 'nextChapterNumber'));
    }

    public function storeChapter(Request $request, Story $story)
    {
        if ($story->user_id != Auth::id()) {
            return redirect()->route('user.author.index')
                ->with('error', 'Bạn không có quyền thêm chương cho truyện này.');
        }

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
            'password_hint' => 'nullable|string|max:500',
            'has_password' => 'required_if:is_free,1|boolean',
            'scheduled_publish_at' => 'nullable|date',
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
            'status.required' => 'Vui lòng chọn trạng thái chương',
        ]);

        try {
            $proposedSlug = 'chuong-' . $request->number . '-' . Str::slug(Str::limit($request->title, 100));

            if ($story->chapters()->where('slug', $proposedSlug)->exists()) {
                return redirect()->back()
                    ->with('error', 'Tiêu đề chương này tạo ra slug đã tồn tại. Vui lòng sử dụng tiêu đề khác.')
                    ->withInput();
            }

            $scheduledPublishAt = null;
            
            if ($request->status == 'draft' && !empty($request->scheduled_publish_at)) {
                $scheduledTime = Carbon::createFromFormat('Y-m-d\TH:i', $request->scheduled_publish_at, 'Asia/Ho_Chi_Minh');
                $scheduledPublishAt = $scheduledTime->format('Y-m-d H:i:s');
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
                'password' => ($request->is_free && $request->has_password) ? $this->encryptChapterPassword($request->password) : null,
                'password_hint' => ($request->is_free && $request->has_password) ? $request->password_hint : null,
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
        $batchContent = trim($batchContent);
        if (empty($batchContent)) {
            return [];
        }

        $batchContent = strip_tags($batchContent);
        
        $batchContent = html_entity_decode($batchContent, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        $batchContent = preg_replace('/\r\n|\r/', "\n", $batchContent);
        
        // Updated pattern to handle all variations:
        // 1. "Chương X: Title" (with colon, no space)
        // 2. "Chương X" (without title)
        // 3. Multiple blank lines between chapters
        // 4. Spaces before "Chương"
        $pattern = '/\s*Chương\s+(\d+)(?:\s*:\s*([^\r\n]*))?[\r\n]+([\s\S]*?)(?=[\r\n]+\s*Chương\s+\d+|\z)/';

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

    // Function tạo slug unique cho chương
    private function generateUniqueSlug($storyId, $chapterNumber, $title, $existingSlugs = [])
    {
        $baseSlug = 'chuong-' . $chapterNumber . '-' . Str::slug(Str::limit($title, 100));
        $slug = $baseSlug;
        $counter = 1;

        // Kiểm tra trùng với existing slugs
        while (in_array($slug, $existingSlugs)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        // Kiểm tra trùng với database
        while (Chapter::where('story_id', $storyId)->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    // Xử lý lưu nhiều chương cùng lúc
    public function storeBatchChapters(Request $request, Story $story)
    {
        // Kiểm tra nếu truyện không thuộc về người dùng hiện tại
        if ($story->user_id != Auth::id()) {
            return redirect()->route('user.author.index')
                ->with('error', 'Bạn không có quyền thêm chương cho truyện này.');
        }

        $now = now('Asia/Ho_Chi_Minh');
        $request->validate([
            'batch_content' => 'required',
            'is_free' => 'required|boolean',
            'price' => 'required_if:is_free,0|nullable|integer|min:1',
            'password' => 'nullable|required_if:has_password,1|string|max:50',
            'has_password' => 'required_if:is_free,1|boolean',
            'scheduled_publish_at' => 'nullable|date',
            'hours_interval' => 'nullable|numeric|min:0|max:168',
            'status' => 'required|in:draft,published',
            'chapter_schedules' => 'nullable|array',
            'chapter_schedules.*' => 'nullable|date',
        ], [
            'batch_content.required' => 'Nội dung các chương không được để trống',
            'is_free.required' => 'Vui lòng chọn hình thức chương',
            'price.required_if' => 'Vui lòng nhập giá xu cho chương',
            'price.integer' => 'Giá xu phải là số nguyên',
            'price.min' => 'Giá xu phải lớn hơn 0',
            'password.required_if' => 'Vui lòng nhập mật khẩu cho chương',
            'scheduled_publish_at.date' => 'Thời gian hẹn giờ không hợp lệ',
            'hours_interval.numeric' => 'Khoảng cách giờ phải là số',
            'hours_interval.min' => 'Khoảng cách giờ phải lớn hơn hoặc bằng 0',
            'hours_interval.max' => 'Khoảng cách giờ không được quá 168 giờ (1 tuần)',
            'status.required' => 'Vui lòng chọn trạng thái chương',
            'chapter_schedules.*.date' => 'Thời gian hẹn giờ không hợp lệ',
        ]);

        $chapters = $this->parseChaptersFromBatchContent($request->batch_content);

        if (empty($chapters)) {
            return back()->with('error', 'Không thể tách nội dung thành các chương. Vui lòng kiểm tra lại định dạng.')->withInput();
        }

        $existingNumbers = $story->chapters()->pluck('number')->toArray();
        $existingSlugs = $story->chapters()->pluck('slug')->toArray();

        $errors = [
            'number' => [],
        ];

        // Chỉ kiểm tra trùng số chương, không kiểm tra trùng tiêu đề
        foreach ($chapters as $chapter) {
            if (in_array($chapter['number'], $existingNumbers)) {
                $errors['number'][] = "Chương {$chapter['number']}";
            }
        }

        if (!empty($errors['number'])) {
            $msg = "Phát hiện chương bị trùng lặp:";
            if ($errors['number']) $msg .= " Trùng số chương: " . implode(', ', $errors['number']) . ".";
            return back()->with('error', $msg . ' Vui lòng chỉnh sửa và thử lại.')->withInput();
        }

        // Lấy dữ liệu lịch đăng cho từng chương
        $chapterSchedules = $request->input('chapter_schedules', []);
        $baseScheduleTime = $request->scheduled_publish_at;
        $hoursInterval = $request->hours_interval;

        DB::beginTransaction();
        try {
            $userId = Auth::id();
            $isFree = $request->is_free;
            $hasPassword = $request->has_password;
            $password = $hasPassword && $isFree ? $this->encryptChapterPassword($request->password) : null;

            $successCount = 0;

            // Sắp xếp chapters theo số chương để tính toán thời gian đúng
            usort($chapters, function($a, $b) {
                return $a['number'] - $b['number'];
            });

            // Tính toán thời gian xuất bản cho các chương không có lịch riêng
            $chaptersWithoutCustomSchedule = [];
            foreach ($chapters as $chapter) {
                if (!isset($chapterSchedules[$chapter['number']]) || empty($chapterSchedules[$chapter['number']])) {
                    $chaptersWithoutCustomSchedule[] = $chapter['number'];
                }
            }

            foreach ($chapters as $index => $chapter) {
                $slug = $this->generateUniqueSlug($story->id, $chapter['number'], $chapter['title'], $existingSlugs);

                $finalScheduleDate = null;
                
                if (isset($chapterSchedules[$chapter['number']]) && !empty($chapterSchedules[$chapter['number']])) {
                    $scheduleTime = Carbon::createFromFormat('Y-m-d\TH:i', $chapterSchedules[$chapter['number']], 'Asia/Ho_Chi_Minh');
                    $finalScheduleDate = $scheduleTime->format('Y-m-d H:i:s');
                } elseif ($request->status == 'draft' && $baseScheduleTime) {
                    if ($hoursInterval && $hoursInterval > 0) {
                        $positionInList = array_search($chapter['number'], $chaptersWithoutCustomSchedule);
                        if ($positionInList !== false) {
                            $baseTime = Carbon::createFromFormat('Y-m-d\TH:i', $baseScheduleTime, 'Asia/Ho_Chi_Minh');
                            $baseTime->addHours($positionInList * $hoursInterval);
                            $scheduleTime = $baseTime;
                        } else {
                            $scheduleTime = Carbon::createFromFormat('Y-m-d\TH:i', $baseScheduleTime, 'Asia/Ho_Chi_Minh');
                        }
                    } else {
                        $scheduleTime = Carbon::createFromFormat('Y-m-d\TH:i', $baseScheduleTime, 'Asia/Ho_Chi_Minh');
                    }
                    
                    $finalScheduleDate = $scheduleTime->format('Y-m-d H:i:s');
                }

                $story->chapters()->create([
                    'slug' => $slug,
                    'title' => $chapter['title'],
                    'content' => $chapter['content'],
                    'number' => $chapter['number'],
                    'status' => $request->status,
                    'user_id' => $userId,
                    'updated_content_at' => now(),
                    'is_free' => $isFree,
                    'price' => $isFree ? null : $request->price,
                    'password' => $password,
                    'scheduled_publish_at' => $finalScheduleDate,
                ]);

                $existingNumbers[] = $chapter['number'];
                $existingSlugs[] = $slug;
                $successCount++;
            }

            DB::commit();

            $message = "Đã tạo thành công {$successCount} chương mới.";

            // Thêm thông tin về lịch xuất bản nếu có
            if ($baseScheduleTime && $hoursInterval > 0) {
                $message .= " Các chương sẽ được xuất bản tự động theo lịch đã thiết lập.";
            }

            return redirect()->route('user.author.stories.chapters', $story->id)
                ->with('success', $message);
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

        $now = now('Asia/Ho_Chi_Minh');
        $request->validate([
            'title' => 'nullable|max:255',
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
            'password_hint' => 'nullable|string|max:500',
            'has_password' => 'required_if:is_free,1|boolean',
            'scheduled_publish_at' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) use ($now) {
                    if (!empty($value)) {
                        $scheduledTime = Carbon::parse($value, 'Asia/Ho_Chi_Minh');
                        if ($scheduledTime->lte($now)) {
                            $fail('Thời gian hẹn giờ phải sau thời điểm hiện tại (' . $now->format('d/m/Y H:i') . ')');
                        }
                    }
                },
            ],
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
                if (!empty($request->password)) {
                    $password = $this->encryptChapterPassword($request->password);
                }
                else if (!empty($chapter->password)) {
                    $password = $chapter->password;
                }
            }

            $scheduledPublishAt = null;
            
            if ($request->status == 'draft' && !empty($request->scheduled_publish_at)) {
                $scheduledTime = Carbon::createFromFormat('Y-m-d\TH:i', $request->scheduled_publish_at, 'Asia/Ho_Chi_Minh');
                $scheduledPublishAt = $scheduledTime->format('Y-m-d H:i:s');
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
                'password_hint' => ($request->is_free && $request->has_password) ? $request->password_hint : null,
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

    public function bulkDeleteChapters(Request $request, Story $story)
    {
        if ($story->user_id != Auth::id()) {
            return redirect()->route('user.author.stories.chapters', $story->id)
                ->with('error', 'Bạn không có quyền truy cập truyện này.');
        }

        // Ưu tiên sử dụng selected_chapters_by_range nếu có (từ chọn theo phạm vi)
        if ($request->has('selected_chapters_by_range')) {
            $selectedChapterIdsJson = $request->selected_chapters_by_range;
            $selectedChapterIds = json_decode($selectedChapterIdsJson, true);
            
            if (!is_array($selectedChapterIds) || empty($selectedChapterIds)) {
                return redirect()->back()
                    ->with('error', 'Danh sách chương được chọn không hợp lệ.');
            }
        } else {
            $request->validate([
                'selected_chapters' => 'required|array|min:1',
                'selected_chapters.*' => 'exists:chapters,id',
            ], [
                'selected_chapters.required' => 'Vui lòng chọn ít nhất một chương để xóa.',
                'selected_chapters.min' => 'Vui lòng chọn ít nhất một chương để xóa.',
            ]);

            $selectedChapterIds = $request->selected_chapters;
        }

        // Check if story has been purchased (combo purchase)
        $storyHasPurchases = $story->purchases()->exists();

        // Validate all chapter IDs exist and filter out chapters with purchases
        $validChapters = Chapter::whereIn('id', $selectedChapterIds)
            ->where('story_id', $story->id)
            ->withCount('purchases')
            ->get();

        // Filter out chapters that have purchases (direct or via story combo)
        $deletableChapters = $validChapters->filter(function($chapter) use ($storyHasPurchases) {
            $hasDirectPurchases = $chapter->purchases_count > 0;
            // If story has purchases (combo) and chapter is VIP, it cannot be deleted
            $hasStoryPurchases = $storyHasPurchases && !$chapter->is_free;
            return !$hasDirectPurchases && !$hasStoryPurchases;
        });

        $chaptersWithPurchases = $validChapters->filter(function($chapter) use ($storyHasPurchases) {
            $hasDirectPurchases = $chapter->purchases_count > 0;
            $hasStoryPurchases = $storyHasPurchases && !$chapter->is_free;
            return $hasDirectPurchases || $hasStoryPurchases;
        });

        if ($deletableChapters->isEmpty()) {
            $message = 'Không tìm thấy chương nào để xóa.';
            if ($chaptersWithPurchases->isNotEmpty()) {
                if ($storyHasPurchases) {
                    $message = 'Tất cả các chương VIP được chọn đều không thể xóa vì truyện này đã có người mua combo.';
                } else {
                    $message = 'Tất cả các chương được chọn đều đã có người mua và không thể xóa.';
                }
            }
            return redirect()->back()
                ->with('error', $message);
        }

        $selectedChapterIds = $deletableChapters->pluck('id')->toArray();
        $currentPage = $request->current_page ?? 1;

        try {
            DB::beginTransaction();

            $totalChaptersBeforeDelete = $story->chapters()->count();

            $deletedCount = Chapter::whereIn('id', $selectedChapterIds)
                ->where('story_id', $story->id)
                ->delete();

            $perPage = 2;
            $totalChaptersAfterDelete = $totalChaptersBeforeDelete - $deletedCount;
            $maxPage = max(1, (int) ceil($totalChaptersAfterDelete / $perPage));

            $redirectPage = min($currentPage, $maxPage);

            DB::commit();

            $successMessage = "Đã xóa thành công {$deletedCount} chương.";
            if ($chaptersWithPurchases->isNotEmpty()) {
                $successMessage .= " ({$chaptersWithPurchases->count()} chương đã có người mua không được xóa)";
            }

            return redirect()->route('user.author.stories.chapters', ['story' => $story->id, 'page' => $redirectPage])
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Bulk chapters deletion failed', [
                'story_id' => $story->id,
                'chapter_ids' => $selectedChapterIds,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->route('user.author.stories.chapters', ['story' => $story->id, 'page' => $currentPage])
                ->with('error', 'Có lỗi xảy ra khi xóa chương. Vui lòng thử lại.');
        }
    }

    public function destroyChapter(Request $request, Story $story, $chapterId)
    {
        if ($story->user_id != Auth::id()) {
            return redirect()->route('user.author.index')
                ->with('error', 'Bạn không có quyền xóa chương của truyện này.');
        }

        $chapter = $story->chapters()->findOrFail($chapterId);
        $currentPage = $request->input('page', 1);

        // Kiểm tra xem chương đã có người mua VIP chưa
        $hasPurchases = $chapter->purchases()->exists();
        if ($hasPurchases) {
            return redirect()->route('user.author.stories.chapters', ['story' => $story->id, 'page' => $currentPage])
                ->with('error', 'Không thể xóa chương này vì đã có người mua VIP. Vui lòng liên hệ admin nếu cần hỗ trợ.');
        }

        try {
            $chapterNumber = $chapter->number;
            $chapterTitle = $chapter->title;

            $totalChaptersBeforeDelete = $story->chapters()->count();

            $chapter->delete();

            $perPage = 2;
            $totalChaptersAfterDelete = $totalChaptersBeforeDelete - 1;
            $maxPage = max(1, (int) ceil($totalChaptersAfterDelete / $perPage));

            $redirectPage = min($currentPage, $maxPage);



            return redirect()->route('user.author.stories.chapters', ['story' => $story->id, 'page' => $redirectPage])
                ->with('success', "Đã xóa chương {$chapterNumber}: {$chapterTitle} thành công.");
        } catch (\Exception $e) {
            Log::error('Error deleting chapter: ' . $e->getMessage());

            return redirect()->route('user.author.stories.chapters', ['story' => $story->id, 'page' => $currentPage])
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

        $storyRevenueStats = DB::table('stories')
            ->select(
                'stories.id',
                'stories.title',
                'stories.slug',
                DB::raw('COALESCE(chapter_revenue.total, 0) as chapter_revenue'),
                DB::raw('COALESCE(story_revenue.total, 0) as story_revenue'),
                DB::raw('COALESCE(chapter_revenue.total, 0) + COALESCE(story_revenue.total, 0) as total_revenue')
            )
            ->leftJoin(DB::raw('(
                SELECT 
                    chapters.story_id,
                    SUM(chapter_purchases.amount_received) as total
                FROM chapters
                INNER JOIN chapter_purchases ON chapters.id = chapter_purchases.chapter_id
                GROUP BY chapters.story_id
            ) as chapter_revenue'), 'stories.id', '=', 'chapter_revenue.story_id')
            ->leftJoin(DB::raw('(
                SELECT 
                    story_id,
                    SUM(amount_received) as total
                FROM story_purchases
                GROUP BY story_id
            ) as story_revenue'), 'stories.id', '=', 'story_revenue.story_id')
            ->where('stories.user_id', Auth::id())
            ->orderBy('total_revenue', 'desc')
            ->paginate(10);

        return view('pages.information.author.author_revenue', compact(
            'years',
            'grandTotal',
            'topStories',
            'topChapters',
            'lastMonthRevenue',
            'revenueChangePercent',
            'revenueIncreased',
            'storyRevenueStats'
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

        if (empty($month) || $month === '' || $month === null) {
            return $this->getYearlyRevenueData($year);
        }

        return $this->getMonthlyRevenueData($year, $month);
    }

    private function getYearlyRevenueData($year)
    {
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

        $storyRevenue = DB::table('story_purchases')
            ->join('stories', 'story_purchases.story_id', '=', 'stories.id')
            ->where('stories.user_id', Auth::id())
            ->whereYear('story_purchases.created_at', $year)
            ->selectRaw('MONTH(story_purchases.created_at) as month, SUM(story_purchases.amount_received) as total')
            ->groupBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();

        $data = [];
        $labels = [];
        $chapterData = [];
        $storyData = [];
        $totalData = [];

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

    private function getMonthlyRevenueData($year, $month)
    {
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

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

        $data = [];
        $labels = [];
        $chapterData = [];
        $storyData = [];
        $totalData = [];

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

        // Check for duplicate chapter numbers
        $duplicateNumbers = Chapter::where('story_id', $story->id)
            ->whereIn('number', $chapterNumbers)
            ->pluck('number')
            ->toArray();

        return response()->json([
            'duplicate_numbers' => $duplicateNumbers,
            'duplicate_titles' => [] // Không kiểm tra trùng tiêu đề nữa
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
            ->select('id', 'title', 'number', 'is_free', 'price', 'password')
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

        // Ưu tiên sử dụng selected_chapters_by_range nếu có (từ chọn theo phạm vi)
        if ($request->has('selected_chapters_by_range')) {
            $selectedChapterIdsJson = $request->selected_chapters_by_range;
            $selectedChapterIds = json_decode($selectedChapterIdsJson, true);
            
            if (!is_array($selectedChapterIds) || empty($selectedChapterIds)) {
                return redirect()->back()
                    ->with('error', 'Danh sách chương được chọn không hợp lệ.');
            }

            // Validate all chapter IDs exist (allow update even if purchased)
            $validChapterIds = Chapter::whereIn('id', $selectedChapterIds)
                ->where('story_id', $story->id)
                ->where('status', 'published')
                ->pluck('id')
                ->toArray();

            if (empty($validChapterIds)) {
                return redirect()->back()
                    ->with('error', 'Không tìm thấy chương nào để cập nhật giá.');
            }

            $selectedChapterIds = $validChapterIds;
            
            // Validate all_price is provided (for range selection, always use all_same mode)
            $request->validate([
                'all_price' => 'nullable|numeric|min:0',
            ], [
                'all_price.min' => 'Giá tối thiểu là 0 xu (miễn phí).',
            ]);
        } else {
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
        }

        // Only validate update_type if not using range selection
        if (!$request->has('selected_chapters_by_range')) {
            $request->validate([
                'update_type' => 'required|in:all_same,individual',
                'all_price' => 'required_if:update_type,all_same|nullable|numeric|min:0',
                'chapter_prices' => 'required_if:update_type,individual|array',
                'chapter_prices.*' => 'nullable|numeric|min:0',
            ], [
                'update_type.required' => 'Vui lòng chọn loại cập nhật.',
                'all_price.required_if' => 'Vui lòng nhập giá áp dụng cho tất cả.',
                'all_price.min' => 'Giá tối thiểu là 0 xu (miễn phí).',
                'chapter_prices.required_if' => 'Vui lòng nhập giá cho từng chương.',
                'chapter_prices.*.min' => 'Giá tối thiểu là 0 xu (miễn phí).',
            ]);
        }

        try {
            DB::beginTransaction();

            // If using selected_chapters_by_range, automatically use 'all_same' mode
            $updateType = $request->has('selected_chapters_by_range') ? 'all_same' : $request->update_type;

            if ($updateType === 'all_same') {
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

    /**
     * Author đề cử truyện lên trang chủ
     */
    public function featured(Request $request, Story $story)
    {
        if ($story->user_id !== auth()->id()) {
            return back()->with('error', 'Bạn không có quyền thực hiện hành động này.');
        }

        if ($story->status !== 'published') {
            return back()->with('error', 'Chỉ có thể đề cử truyện đã được xuất bản.');
        }

        if ($story->is_featured || $story->isCurrentlyAdminFeatured()) {
            return back()->with('error', 'Truyện đã được admin đề cử, không thể đề cử thêm.');
        }

        if ($story->isCurrentlyAuthorFeatured()) {
            return back()->with('error', 'Truyện đang được đề cử, vui lòng chờ hết hạn.');
        }

        $featuredPrice = \App\Models\Config::getConfig('story_featured_price', 100);
        $featuredDuration = \App\Models\Config::getConfig('story_featured_duration', 7);

        if (auth()->user()->coins < $featuredPrice) {
            return back()->with('error', 'Không đủ xu để đề cử truyện. Cần ' . number_format($featuredPrice) . ' xu.');
        }

        try {
            DB::beginTransaction();

            $coinService = new \App\Services\CoinService();
            
            $featuredRecord = \App\Models\StoryFeatured::createFeatured(
                $story->id,
                auth()->id(),
                \App\Models\StoryFeatured::TYPE_AUTHOR,
                $featuredDuration,
                $featuredPrice,
                null,
                "Đề cử truyện lên trang chủ"
            );

            $coinService->subtractCoins(
                auth()->user(),
                $featuredPrice,
                \App\Models\CoinHistory::TYPE_FEATURED_STORY,
                "Đề cử truyện '{$story->title}' lên trang chủ ({$featuredDuration} ngày)",
                $featuredRecord
            );

            DB::commit();

            return back()->with('success', "Đã đề cử truyện '{$story->title}' lên trang chủ thành công! Truyện sẽ được hiển thị trong {$featuredDuration} ngày.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Author featured story failed', [
                'story_id' => $story->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Có lỗi xảy ra khi đề cử truyện. Vui lòng thử lại.');
        }
    }

    public function getStoryRevenueStats(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = 10;

        $storyRevenueStats = DB::table('stories')
            ->select(
                'stories.id',
                'stories.title',
                'stories.slug',
                DB::raw('COALESCE(chapter_revenue.total, 0) as chapter_revenue'),
                DB::raw('COALESCE(story_revenue.total, 0) as story_revenue'),
                DB::raw('COALESCE(chapter_revenue.total, 0) + COALESCE(story_revenue.total, 0) as total_revenue')
            )
            ->leftJoin(DB::raw('(
                SELECT 
                    chapters.story_id,
                    SUM(chapter_purchases.amount_received) as total
                FROM chapters
                INNER JOIN chapter_purchases ON chapters.id = chapter_purchases.chapter_id
                GROUP BY chapters.story_id
            ) as chapter_revenue'), 'stories.id', '=', 'chapter_revenue.story_id')
            ->leftJoin(DB::raw('(
                SELECT 
                    story_id,
                    SUM(amount_received) as total
                FROM story_purchases
                GROUP BY story_id
            ) as story_revenue'), 'stories.id', '=', 'story_revenue.story_id')
            ->where('stories.user_id', Auth::id())
            ->orderBy('total_revenue', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json($storyRevenueStats);
    }

    /**
     * Get chapter IDs by range with purchase check
     */
    public function getChaptersByRange(Request $request, Story $story)
    {
        if ($story->user_id != Auth::id()) {
            return response()->json(['error' => 'Bạn không có quyền thực hiện hành động này.'], 403);
        }

        $request->validate([
            'from' => 'required|integer|min:1',
            'to' => 'required|integer|min:1|gte:from'
        ]);

        $from = $request->input('from');
        $to = $request->input('to');

        // Check if story has been purchased (combo purchase)
        $storyHasPurchases = $story->purchases()->exists();

        $chapters = $story->chapters()
            ->whereBetween('number', [$from, $to])
            ->orderBy('number', 'desc')
            ->get();

        $chapterData = [];
        
        foreach ($chapters as $chapter) {
            $chapterData[$chapter->number] = $chapter->id;
        }

        return response()->json([
            'success' => true,
            'chapters' => $chapterData,
            'count' => count($chapterData)
        ]);
    }

    /**
     * Upload image for story notice (used in CKEditor)
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'upload' => 'required|image|max:5120',
        ]);

        try {
            $image = $request->file('upload');
            $now = \Carbon\Carbon::now();
            $yearMonth = $now->format('Y/m');
            $timestamp = $now->format('YmdHis');
            $randomString = Str::random(8);
            $fileName = "{$timestamp}_{$randomString}";

            Storage::disk('public')->makeDirectory("stories/temp/{$yearMonth}");

            $img = Image::make($image->getRealPath());
            
            if ($img->width() > 1200 || $img->height() > 1200) {
                $img->resize(1200, 1200, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            $img->encode('webp', 80);
            
            $tempPath = "stories/temp/{$yearMonth}/{$fileName}.webp";
            Storage::disk('public')->put($tempPath, $img->stream());

            $url = Storage::url($tempPath);
            $fullUrl = asset($url);

            return response()->json([
                'uploaded' => true,
                'url' => $fullUrl,
                'tempPath' => $tempPath
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'uploaded' => false,
                'error' => [
                    'message' => 'Có lỗi xảy ra khi upload hình ảnh: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    private function processStoryNoticeImages($storyNotice, $storyId = null)
    {
        if (empty($storyNotice)) {
            return $storyNotice;
        }

        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $storyNotice, $matches);
        
        if (empty($matches[1])) {
            return $storyNotice;
        }

        $now = \Carbon\Carbon::now();
        $yearMonth = $now->format('Y/m');
        $storyFolder = $storyId ? "stories/{$storyId}/notice" : "stories/{$now->format('YmdHis')}/notice";

        foreach ($matches[1] as $imageUrl) {
            if (strpos($imageUrl, '/storage/stories/temp/') !== false) {
                $tempPath = str_replace(asset('/storage/'), '', $imageUrl);
                $tempPath = str_replace('/storage/', '', $tempPath);

                if (Storage::disk('public')->exists($tempPath)) {
                    Storage::disk('public')->makeDirectory("{$storyFolder}/{$yearMonth}");
                    
                    $fileName = basename($tempPath);
                    $newPath = "{$storyFolder}/{$yearMonth}/{$fileName}";
                    
                    Storage::disk('public')->move($tempPath, $newPath);
                    
                    $newUrl = Storage::url($newPath);
                    $storyNotice = str_replace($imageUrl, asset($newUrl), $storyNotice);
                }
            }
        }

        return $storyNotice;
    }

    private function extractStoryNoticeImages($storyNotice)
    {
        if (empty($storyNotice)) {
            return [];
        }

        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $storyNotice, $matches);
        
        $images = [];
        foreach ($matches[1] as $url) {
            if (strpos($url, '/storage/') !== false) {
                $path = str_replace(asset('/storage/'), '', $url);
                $path = str_replace('/storage/', '', $path);
                if (!empty($path)) {
                    $images[] = $path;
                }
            }
        }
        
        return $images;
    }

    private function deleteUnusedStoryNoticeImages($oldImages, $newImages)
    {
        $imagesToDelete = array_diff($oldImages, $newImages);
        
        foreach ($imagesToDelete as $imagePath) {
            if (strpos($imagePath, 'stories/temp/') === false && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
                
                $dir = dirname($imagePath);
                if (count(Storage::disk('public')->files($dir)) === 0 && count(Storage::disk('public')->directories($dir)) === 0) {
                    Storage::disk('public')->deleteDirectory($dir);
                }
            }
        }
    }
}

