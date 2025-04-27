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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\ReadingHistoryService;

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
            'isSearch' => true
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
        // Truy vấn truyện với chương mới nhất
        $query = Story::with(['latestChapter' => function ($query) {
            $query->select('id', 'story_id', 'title', 'slug', 'number', 'views', 'created_at', 'status')
                ->where('status', 'published'); // Chỉ lấy chương đã xuất bản
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
            ->whereHas('chapters', function ($query) {
                $query->where('status', 'published'); // Chỉ lấy truyện có chương đã xuất bản
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
                ->where('status', 'published') // Chỉ xét chương đã xuất bản
                ->latest()
                ->limit(1);
        })
            ->take(20)
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

    public function chapterByStory($storySlug, $chapterSlug)
    {
        // First find the story by slug
        $story = Story::where('slug', $storySlug)->firstOrFail();

        // Then find the chapter that belongs to this story
        $query = Chapter::where('slug', $chapterSlug)
            ->where('story_id', $story->id);

        // Apply permissions
        if (auth()->check()) {
            if (in_array(auth()->user()->role, ['admin', 'mod'])) {
                // Admin and mod can see all chapters
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

        // Find next and previous chapters
        $nextChapterQuery = Chapter::where('story_id', $story->id)
            ->where('number', '>', $chapter->number)
            ->orderBy('number', 'asc');

        $prevChapterQuery = Chapter::where('story_id', $story->id)
            ->where('number', '<', $chapter->number)
            ->orderBy('number', 'desc');

        // Apply published filter for non-admin/mod users    
        if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'mod'])) {
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

        if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'mod'])) {
            $recentChaptersQuery->where('status', 'published');
        }

        $recentChapters = $recentChaptersQuery->get();

        // Lưu tiến độ đọc
        $readingService = new ReadingHistoryService();
        $readingService->saveReadingProgress($story, $chapter);

        // Lấy danh sách truyện đọc gần đây
        $recentReads = $readingService->getRecentReadings(5);

        return view('pages.chapter', compact(
            'chapter',
            'story',
            'nextChapter',
            'prevChapter',
            'recentChapters',
            'recentReads'
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
