<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Story;
use App\Models\Banner;
use App\Models\Rating;
use App\Models\Status;
use App\Models\Chapter;
use App\Models\Comment;
use App\Models\Socials;
use App\Models\Category;
use App\Models\UserReading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\ReadingHistoryService;
use App\Models\ChapterPurchase;
use App\Models\StoryPurchase;

class HomeController extends Controller
{

    public function searchHeader(Request $request)
    {
        $query = $request->input('query');

        // Search in stories and chapters
        $stories = Story::query()
            ->published()
            ->where('title', 'LIKE', "%{$query}%")
            ->orWhereHas('chapters', function ($q) use ($query) {
                $q->where('status', 'published')
                    ->where('title', 'LIKE', "%{$query}%")
                    ->orWhere('content', 'LIKE', "%{$query}%");
            })
            ->orWhereHas('categories', function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->with(['categories', 'chapters'])
            ->paginate(20);

        return view('pages.search.results', [
            'stories' => $stories,
            'query' => $query,
            'isSearch' => true,
            'searchType' => 'general' // Thêm thông tin loại tìm kiếm
        ]);
    }

    public function searchAuthor(Request $request)
    {
        $query = $request->input('query');

        // Search in stories by author name
        $stories = Story::query()
            ->published()
            ->where('author_name', 'LIKE', "%{$query}%")
            ->with(['categories', 'chapters'])
            ->paginate(20);

        return view('pages.search.results', [
            'stories' => $stories,
            'query' => $query,
            'isSearch' => true,
            'searchType' => 'author' // Thêm thông tin loại tìm kiếm
        ]);
    }

    public function searchTranslator(Request $request)
    {
        $query = $request->input('query');

        // Search in stories by translator name
        $stories = Story::query()
            ->published()
            ->where('translator_name', 'LIKE', "%{$query}%")
            ->with(['categories', 'chapters'])
            ->paginate(20);

        return view('pages.search.results', [
            'stories' => $stories,
            'query' => $query,
            'isSearch' => true,
            'searchType' => 'translator' // Thêm thông tin loại tìm kiếm
        ]);
    }

    public function showStoryCategories($slug)
    {

        $category = Category::where('slug', $slug)->firstOrFail();

        $stories = $category->stories()
            ->published()
            ->with(['categories', 'chapters'])
            ->paginate(20);

        return view('pages.search.results', [
            'stories' => $stories,
            'currentCategory' => $category,
            'isSearch' => false
        ]);
    }

    public function index(Request $request)
    {

        // Get banners
        $banners = Banner::active()->get();

        // Get hot stories
        $hotStories = $this->getHotStories($request);

        // Get new stories
        $newStories = $this->getNewStories($request);

        // Get completed stories
        $completedStories = Story::with(['categories'])
            ->published()
            ->where('completed', true)
            ->whereHas('chapters', function ($query) {
                $query->where('status', 'published'); // Chỉ lấy truyện có chương đã xuất bản
            })
            ->select([
                'id',
                'title',
                'slug',
                'cover',
                'completed',
                'updated_at'
            ])
            ->withCount(['chapters' => function ($query) {
                $query->where('status', 'published'); // Chỉ đếm chương đã xuất bản
            }])
            ->latest('updated_at')
            ->take(18)
            ->get();

        //dd($completedStories);

        // Handle AJAX requests
        if ($request->ajax()) {
            if ($request->type === 'hot') {
                return response()->json([
                    'html' => view('components.stories-grid', compact('hotStories'))->render()
                ]);
            } elseif ($request->type === 'new') {
                return response()->json([
                    'html' => view('components.story-list-items', compact('newStories'))->render()
                ]);
            }
        }

        return view('pages.home', compact('hotStories', 'newStories', 'completedStories', 'banners'));
    }

    private function getHotStories($request)
    {
        $query = Story::with(['chapters' => function ($query) {
            $query->select('id', 'story_id', 'views', 'created_at')
                ->where('status', 'published'); // Chỉ lấy chương đã xuất bản
        }])
            ->published()
            ->whereHas('chapters', function ($query) {
                $query->where('status', 'published'); // Chỉ lấy truyện có chương đã xuất bản
            })
            ->select([
                'id',
                'title',
                'slug',
                'cover',
                'completed',
                'description',
                'created_at',
                'updated_at'
            ])
            ->withCount(['chapters' => function ($query) {
                $query->where('status', 'published');
            }])
            ->where('updated_at', '>=', now()->subDays(30));

        // Apply category filter if selected
        if ($request->category_id) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        $hotStories = $query->get()
            ->map(function ($story) {
                $story->hot_score = $this->calculateHotScore($story);
                return $story;
            })
            ->sortByDesc('hot_score')
            ->take(12);
        return $hotStories;
    }

    private function calculateHotScore($story)
    {
        // Get total views of all chapters (đã lọc published trong getHotStories)
        $totalViews = $story->chapters->sum('views');

        // Get average views per chapter
        $avgViews = $story->chapters_count > 0 ?
            $totalViews / $story->chapters_count : 0;

        // Get views in last 7 days
        $recentViews = $story->chapters()
            ->where('status', 'published') // Chỉ đếm chương đã xuất bản
            ->where('created_at', '>=', now()->subDays(7))
            ->sum('views');

        // Calculate chapter frequency (chapters per day)
        $daysActive = max(1, $story->created_at->diffInDays(now()));
        $chapterFrequency = $story->chapters_count / $daysActive;

        // Calculate recency boost (newer stories get higher scores)
        $daysSinceLastUpdate = $story->updated_at->diffInDays(now());
        $recencyBoost = 1 + (1 / max(1, $daysSinceLastUpdate));

        // Calculate final score using weighted factors
        $score = (
            ($totalViews * 0.3) +
            ($avgViews * 0.2) +
            ($recentViews * 0.25) +
            ($chapterFrequency * 15) +
            ($story->chapters_count * 5)
        ) * $recencyBoost;

        return $score;
    }

    private function getNewStories($request)
    {
        $query = Story::with(['latestChapter' => function ($query) {
            $query->select('id', 'story_id', 'title', 'slug', 'number', 'views', 'created_at', 'status')
                ->where('status', 'published');
        }, 'categories'])
            ->published()
            ->select([
                'id',
                'title',
                'slug',
                'cover',
                'status',
                'completed'
            ])
            ->withCount(['chapters' => function ($query) {
                $query->where('status', 'published');
            }])
            ->whereHas('chapters', function ($query) {
                $query->where('status', 'published');
            });

        // Áp dụng bộ lọc danh mục nếu có
        if ($request->category_id) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        // Sắp xếp theo thời gian chương mới nhất
        return $query->orderByDesc(function ($query) {
            $query->select('created_at')
                ->from('chapters')
                ->whereColumn('story_id', 'stories.id')
                ->where('status', 'published')
                ->latest()
                ->limit(1);
        })
            ->take(6)
            ->get();
    }
    public function showStory(Request $request, $slug)
    {
        $story = Story::where('slug', $slug)->firstOrFail();

        // Eager load necessary relationships
        $story->load(['categories']);

        // Get chapters with pagination
        $chapters = Chapter::where('story_id', $story->id)
            ->published()
            ->orderBy('number', 'desc')
            ->paginate(20); // Show 20 chapters per page

        // Calculate stats
        $stats = [
            'total_chapters' => $story->chapters()->published()->count(),
            'total_views' => $story->chapters()->sum('views'),
            'ratings' => [
                'count' => Rating::where('story_id', $story->id)->count(),
                'average' => Rating::where('story_id', $story->id)->avg('rating') ?? 0
            ]
        ];

        // Get story status
        $status = (object)[
            'status' => $story->completed ? 'done' : 'ongoing'
        ];

        // Get category list with story count
        $storyCategories = $story->categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ];
        });

        // Prepare chapters pagination and ranges
        $chapters = $story->chapters()
            ->published()
            ->orderBy('number', 'asc')
            ->paginate(50);

        // Get comments
        $pinnedComments = Comment::with(['user', 'replies.user', 'reactions'])
            ->where('story_id', $story->id)
            ->whereNull('reply_id')
            ->where('is_pinned', true)
            ->latest('pinned_at')
            ->get();

        $regularComments = Comment::with(['user', 'replies.user', 'reactions'])
            ->where('story_id', $story->id)
            ->whereNull('reply_id')
            ->where('is_pinned', false)
            ->latest()
            ->paginate(10);



        return view('pages.story', compact(
            'story',
            'stats',
            'status',
            'chapters',
            'pinnedComments',
            'regularComments',
            'storyCategories'
        ));
    }

    public function getStoryChapters(Request $request, $storyId)
    {
        $story = Story::findOrFail($storyId);

        // Query base
        $chaptersQuery = Chapter::where('story_id', $storyId)
            ->published();

        // Sắp xếp theo thứ tự yêu cầu
        $sortOrder = $request->get('sort_order', 'asc');
        if ($sortOrder === 'asc') {
            $chaptersQuery->orderBy('number', 'asc');
        } else {
            $chaptersQuery->orderBy('number', 'desc');
        }

        // Tìm kiếm nếu có
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $searchNumber = preg_replace('/[^0-9]/', '', $search);

            $chaptersQuery->where(function ($q) use ($search, $searchNumber) {
                $q->where('title', 'like', "%{$search}%");

                if (!empty($searchNumber)) {
                    $q->orWhere('number', $searchNumber);
                }
            });
        }

        $chapters = $chaptersQuery->paginate(50);

        // Nếu là request AJAX, trả về HTML
        if ($request->ajax()) {
            return response()->json([
                'html' => view('components.chapter-items', [
                    'chapters' => $chapters,
                    'story' => $story,
                    'sortOrder' => $sortOrder
                ])->render(),
                'pagination' => view('components.pagination', ['paginator' => $chapters])->render()
            ]);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    public function chapterByStory($storySlug, $chapterSlug)
    {
        // First find the story by slug
        $story = Story::where('slug', $storySlug)->firstOrFail();

        // Then find the chapter that belongs to this story
        $query = Chapter::where('slug', $chapterSlug)
            ->where('story_id', $story->id);

        // Apply permissions
        if (auth()->check()) {
            if (in_array(auth()->user()->role, ['admin', 'mod', 'author'])) {
                // Admin, mod, and author can see all chapters
                $chapter = $query->firstOrFail();
            } else {
                // Regular users can only see published chapters
                $chapter = $query->where('status', 'published')->firstOrFail();
            }
        } else {
            // Guests can only see published chapters
            $chapter = $query->where('status', 'published')->firstOrFail();
        }

        // Get client IP for view count
        $ip = request()->ip();
        $sessionKey = "chapter_view_{$chapter->id}_{$ip}";

        if (!session()->has($sessionKey)) {
            $chapter->increment('views');
            session([$sessionKey => true]);
            session()->put($sessionKey, true, 1440);
        }

        $wordCount = str_word_count(strip_tags($chapter->content), 0, 'àáãạảăắằẳẵặâấầẩẫậèéẹẻẽêềếểễệđìíĩỉịòóõọỏôốồổỗộơớờởỡợùúũụủưứừửữựỳýỵỷỹ');
        $chapter->word_count = $wordCount;

        $chapter->comments_count = Comment::where('story_id', $story->id)->count();

        // Find next and previous chapters
        $nextChapterQuery = Chapter::where('story_id', $story->id)
            ->where('number', '>', $chapter->number)
            ->orderBy('number', 'asc');

        $prevChapterQuery = Chapter::where('story_id', $story->id)
            ->where('number', '<', $chapter->number)
            ->orderBy('number', 'desc');

        // Apply published filter for non-admin/mod users    
        if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'mod', 'author'])) {
            $nextChapterQuery->where('status', 'published');
            $prevChapterQuery->where('status', 'published');
        }

        $nextChapter = $nextChapterQuery->first();
        $prevChapter = $prevChapterQuery->first();

        // Get recent chapters from this story
        $recentChaptersQuery = Chapter::where('story_id', $story->id)
            ->where('id', '!=', $chapter->id)
            ->orderBy('number', 'desc')
            ->take(5);

        if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'mod', 'author'])) {
            $recentChaptersQuery->where('status', 'published');
        }

        $recentChapters = $recentChaptersQuery->get();

        // Lưu tiến độ đọc
        $readingService = new ReadingHistoryService();
        $readingService->saveReadingProgress($story, $chapter);

        // Lấy danh sách truyện đọc gần đây
        $recentReads = $readingService->getRecentReadings(5);

        // Retrieve reading progress if exists
        $userReading = null;
        if (auth()->check()) {
            $userReading = UserReading::where('user_id', auth()->id())
                ->where('story_id', $story->id)
                ->where('chapter_id', $chapter->id)
                ->first();
        } else {
            $deviceKey = $readingService->getOrCreateDeviceKey();
            $userReading = UserReading::where('session_id', $deviceKey)
                ->whereNull('user_id')
                ->where('story_id', $story->id)
                ->where('chapter_id', $chapter->id)
                ->first();
        }
        
        // Pass reading progress to view
        $readingProgress = $userReading ? $userReading->progress_percent : 0;

        // Kiểm tra quyền truy cập nội dung
        $hasAccess = false;
        $hasPurchasedChapter = false;
        $hasPurchasedStory = false;

        // Admin, mod, author và chủ sở hữu truyện luôn có quyền truy cập
        if (auth()->check()) {
            $user = auth()->user();
            
            if (in_array($user->role, ['admin', 'mod']) || 
                ($user->role == 'author' && $story->user_id == $user->id)) {
                $hasAccess = true;
            } else {
                // Kiểm tra nếu đã mua chương này
                $hasPurchasedChapter = ChapterPurchase::where('user_id', $user->id)
                    ->where('chapter_id', $chapter->id)
                    ->exists();
                
                // Kiểm tra nếu đã mua truyện này
                $hasPurchasedStory = StoryPurchase::where('user_id', $user->id)
                    ->where('story_id', $story->id)
                    ->exists();
                
                $hasAccess = $hasPurchasedChapter || $hasPurchasedStory;
            }
        }

        // Nếu chương miễn phí
        if (!$chapter->price || $chapter->price == 0) {
            $hasAccess = true;
        }
        
        // Ẩn nội dung nếu không có quyền truy cập
        if (!$hasAccess) {
            // Không xóa content hoàn toàn để có thể hiển thị phần preview nếu cần
            $originalContent = $chapter->content;
            
            // Lấy một phần đầu của nội dung làm preview (ví dụ: 10% đầu tiên)
            $previewLength = min(300, intval(strlen($originalContent) * 0.1));
            $chapter->preview_content = substr($originalContent, 0, $previewLength) . '...';
        }

        // Xử lý nội dung dựa trên quyền truy cập
        if (!$hasAccess) {
            // Xóa hoàn toàn nội dung để đảm bảo bảo mật
            $chapter->content = '';
        }

        // Get comments
        $pinnedComments = Comment::with(['user', 'replies.user', 'reactions'])
            ->where('story_id', $story->id)
            ->whereNull('reply_id')
            ->where('is_pinned', true)
            ->latest('pinned_at')
            ->get();

        $regularComments = Comment::with(['user', 'replies.user', 'reactions'])
            ->where('story_id', $story->id)
            ->whereNull('reply_id')
            ->where('is_pinned', false)
            ->latest()
            ->paginate(10);

        return view('pages.chapter', compact(
            'chapter',
            'story',
            'nextChapter',
            'prevChapter',
            'recentChapters',
            'recentReads',
            'readingProgress',
            'pinnedComments',
            'regularComments',
            'hasAccess',
            'hasPurchasedChapter',
            'hasPurchasedStory'
        ));
    }

    public function searchChapters(Request $request)
    {
        $searchTerm = $request->search;
        $storyId = $request->story_id;

        $query = Chapter::query();

        // Filter by story ID when provided
        if ($storyId) {
            $query->where('story_id', $storyId);
        }

        // Visibility check
        if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'mod'])) {
            $query->where('status', 'published');
        }

        if ($searchTerm) {
            $searchNumber = preg_replace('/[^0-9]/', '', $searchTerm);

            $query->where(function ($q) use ($searchTerm, $searchNumber) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('content', 'like', "%{$searchTerm}%");

                if ($searchNumber !== '') {
                    $q->orWhere('number', $searchNumber);
                }
            });
        }

        $chapters = $query->orderBy('number', 'desc')->take(20)->get();

        return response()->json([
            'html' => view('components.search-results', compact('chapters'))->render()
        ]);
    }
}
