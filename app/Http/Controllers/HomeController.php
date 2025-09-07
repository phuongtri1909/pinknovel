<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
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
use App\Constants\CacheKeys;
use Illuminate\Http\Request;
use App\Models\StoryPurchase;
use App\Models\ChapterPurchase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\ReadingHistoryService;
use Illuminate\Pagination\LengthAwarePaginator;

class HomeController extends Controller
{

    public function searchHeader(Request $request)
    {
        $query = trim((string) $request->input('query'));

        // Search in stories and chapters
        $storiesQuery = Story::query()
            ->published()
            ->where(function ($outer) use ($query) {
                $outer->where('title', 'LIKE', "%{$query}%")
                    ->orWhereHas('chapters', function ($q) use ($query) {
                        $q->where('status', 'published')
                            ->where(function ($c) use ($query) {
                                $c->where('title', 'LIKE', "%{$query}%")
                                    ->orWhere('content', 'LIKE', "%{$query}%");
                            });
                    })
                    ->orWhereHas('categories', function ($q) use ($query) {
                        $q->where('name', 'LIKE', "%{$query}%");
                    });
            })
            ->with([
                'categories:id,name,slug,is_main',
                'chapters' => function ($query) {
                    $query->select('id', 'story_id', 'views')
                        ->where('status', 'published');
                },
                'latestChapter' => function ($query) {
                    $query->select('id', 'story_id', 'number', 'created_at')
                        ->where('status', 'published');
                }
            ])
            ->select([
                'stories.id', 'stories.title', 'stories.slug', 'stories.cover', 'stories.cover_medium',
                'stories.completed', 'stories.author_name', 'stories.description', 'stories.updated_at', 'stories.reviewed_at'
            ])
            ->distinct('stories.id');

        // Apply advanced search filters
        $storiesQuery = $this->applyAdvancedFilters($storiesQuery, $request);

        $stories = $storiesQuery->paginate(20);

        return view('pages.search.results', [
            'stories' => $stories,
            'query' => $query,
            'isSearch' => true,
            'searchType' => 'general',
            'searchUrl' => route('searchHeader'),
            'categories' => Category::orderBy('name')->get()
        ]);
    }

    public function searchAuthor(Request $request)
    {
        $query = $request->input('query');

        // Search in stories by author name
        $storiesQuery = Story::query()
            ->published()
            ->where('author_name', 'LIKE', "%{$query}%")
            ->with([
                'categories:id,name,slug,is_main',
                'chapters' => function ($query) {
                    $query->select('id', 'story_id', 'views')
                        ->where('status', 'published');
                },
                'latestChapter' => function ($query) {
                    $query->select('id', 'story_id', 'number', 'created_at')
                        ->where('status', 'published');
                }
            ])
            ->select([
                'stories.id', 'stories.title', 'stories.slug', 'stories.cover', 'stories.cover_medium',
                'stories.completed', 'stories.author_name', 'stories.description', 'stories.updated_at', 'stories.reviewed_at'
            ])
            ->distinct('stories.id');

        // Apply advanced search filters
        $storiesQuery = $this->applyAdvancedFilters($storiesQuery, $request);

        $stories = $storiesQuery->paginate(20);

        return view('pages.search.results', [
            'stories' => $stories,
            'query' => $query,
            'isSearch' => true,
            'searchType' => 'author',
            'searchUrl' => route('search.author'),
            'categories' => Category::orderBy('name')->get()
        ]);
    }

    public function searchTranslator(Request $request)
    {
        $query = $request->input('query');

        // Search in stories by translator name
        $storiesQuery = Story::query()
            ->published()
            ->where(function ($outer) use ($query) {
                $outer->whereHas('user', function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%");
                })
                ->orWhere('translator_name', 'LIKE', "%{$query}%");
            })
            ->with([
                'categories:id,name,slug,is_main',
                'chapters' => function ($query) {
                    $query->select('id', 'story_id', 'views')
                        ->where('status', 'published');
                },
                'latestChapter' => function ($query) {
                    $query->select('id', 'story_id', 'number', 'created_at')
                        ->where('status', 'published');
                }
            ])
            ->select([
                'stories.id', 'stories.title', 'stories.slug', 'stories.cover', 'stories.cover_medium',
                'stories.completed', 'stories.author_name', 'stories.description', 'stories.updated_at', 'stories.reviewed_at', 'stories.user_id', 'stories.translator_name'
            ])
            ->distinct('stories.id');

        // Apply advanced search filters
        $storiesQuery = $this->applyAdvancedFilters($storiesQuery, $request);

        $stories = $storiesQuery->paginate(20);

        return view('pages.search.results', [
            'stories' => $stories,
            'query' => $query,
            'isSearch' => true,
            'searchType' => 'translator',
            'searchUrl' => route('search.translator'),
            'categories' => Category::orderBy('name')->get()
        ]);
    }

    public function showStoryCategories(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $storiesQuery = $category->stories()
            ->published()
            ->with([
                'categories',
                'chapters' => function ($query) {
                    $query->select('id', 'story_id', 'views')
                        ->where('status', 'published');
                },
                'latestChapter' => function ($query) {
                    $query->select('id', 'story_id', 'number', 'created_at')
                        ->where('status', 'published');
                }
            ]);

        // Apply advanced search filters
        $storiesQuery = $this->applyAdvancedFilters($storiesQuery, $request);

        $stories = $storiesQuery->paginate(20);

        return view('pages.search.results', [
            'stories' => $stories,
            'currentCategory' => $category,
            'isSearch' => false,
            'searchUrl' => route('categories.story.show', $slug),
            'categories' => Category::orderBy('name')->get()
        ]);
    }

    public function showStoryHot(Request $request)
    {
        // If advanced search filters are applied, use regular query
        if ($request->hasAny(['category', 'sort', 'chapters', 'status'])) {
            $storiesQuery = Story::query()
                ->published()
                ->where('is_featured', true)
                ->whereHas('chapters', function ($query) {
                    $query->where('status', 'published');
                })
                ->with([
                    'categories',
                    'chapters' => function ($query) {
                        $query->select('id', 'story_id', 'views')
                            ->where('status', 'published');
                    },
                    'latestChapter' => function ($query) {
                        $query->select('id', 'story_id', 'number', 'created_at')
                            ->where('status', 'published');
                    }
                ]);

            // Apply advanced search filters
            $storiesQuery = $this->applyAdvancedFilters($storiesQuery, $request);

            $stories = $storiesQuery->paginate(20);
        } else {
            // Use original featured stories logic
            $stories = $this->getFeaturedStoriesForPage();

            $perPage = 20;
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $stories = new LengthAwarePaginator(
                $stories->forPage($currentPage, $perPage),
                $stories->count(),
                $perPage,
                $currentPage,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        }

        return view('pages.search.results', [
            'stories' => $stories,
            'query' => 'hot',
            'isSearch' => false,
            'searchUrl' => route('story.hot'),
            'categories' => Category::orderBy('name')->get()
        ]);
    }

    /**
     * Lấy truyện đề cử cho trang hot (không giới hạn số lượng)
     */
    private function getFeaturedStoriesForPage()
    {
        $query = Story::with([
            'chapters' => function ($query) {
                $query->select('id', 'story_id', 'views', 'created_at')
                    ->where('status', 'published');
            },
            'latestChapter' => function ($query) {
                $query->select('id', 'story_id', 'number', 'created_at')
                    ->where('status', 'published');
            }
        ])
            ->published()
            ->where('is_featured', true)
            ->whereHas('chapters', function ($query) {
                $query->where('status', 'published');
            })
            ->select([
                'id',
                'title',
                'slug',
                'cover',
                'completed',
                'description',
                'created_at',
                'updated_at',
                'cover_medium',
                'author_name',
                'is_featured',
                'featured_order'
            ])
            ->withCount([
                'chapters' => fn($q) => $q->where('status', 'published'),
                'storyPurchases',
                'chapterPurchases',
                'ratings',
                'bookmarks'
            ])
            ->selectSub(function ($q) {
                $q->from('ratings')
                    ->selectRaw('AVG(rating)')
                    ->whereColumn('ratings.story_id', 'stories.id');
            }, 'average_rating')
            ->orderBy('featured_order', 'asc')
            ->orderBy('created_at', 'desc');

        $stories = $query->get();

        // Thêm hot_score
        $stories = $stories->map(function ($story) {
            $story->hot_score = $this->calculateHotScore($story);
            return $story;
        });

        return $stories;
    }

    /**
     * Lấy truyện hot theo thuật toán cho trang hot (fallback)
     */
    private function getHotStoriesForPage()
    {
        $query = Story::with([
            'chapters' => function ($query) {
                $query->select('id', 'story_id', 'views', 'created_at')
                    ->where('status', 'published');
            },
            'latestChapter' => function ($query) {
                $query->select('id', 'story_id', 'number', 'created_at')
                    ->where('status', 'published');
            }
        ])
            ->published()
            ->whereHas('chapters', function ($query) {
                $query->where('status', 'published');
            })
            ->select([
                'id',
                'title',
                'slug',
                'cover',
                'completed',
                'description',
                'created_at',
                'updated_at',
                'cover_medium',
                'author_name'
            ])
            ->withCount([
                'chapters' => fn($q) => $q->where('status', 'published'),
                'storyPurchases',
                'chapterPurchases',
                'ratings',
                'bookmarks'
            ])
            ->selectSub(function ($q) {
                $q->from('ratings')
                    ->selectRaw('AVG(rating)')
                    ->whereColumn('ratings.story_id', 'stories.id');
            }, 'average_rating')
            ->where('updated_at', '>=', now()->subDays(30));

        $stories = $query->get()
            ->map(function ($story) {
                $story->hot_score = $this->calculateHotScore($story);
                return $story;
            })
            ->sortByDesc('hot_score')
            ->values();

        return $stories;
    }

    public function showRatingStories(Request $request)
    {
        $storiesQuery = Story::select('stories.*')
            ->where('status', 'published')
            ->withAvg('ratings as average_rating', 'rating')
            ->with([
                'categories',
                'chapters' => function ($query) {
                    $query->select('id', 'story_id', 'views')
                        ->where('status', 'published');
                },
                'latestChapter' => function ($query) {
                    $query->select('id', 'story_id', 'number', 'created_at')
                        ->where('status', 'published');
                }
            ])
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('ratings')
                    ->whereColumn('ratings.story_id', 'stories.id');
            })
            ->orderByDesc('average_rating')
            ->orderByRaw('COALESCE(stories.reviewed_at, stories.created_at) ASC');

        // Apply advanced search filters
        $storiesQuery = $this->applyAdvancedFilters($storiesQuery, $request);

        $stories = $storiesQuery->paginate(20);

        return view('pages.search.results', [
            'stories' => $stories,
            'query' => 'rating',
            'isSearch' => false,
            'searchUrl' => route('story.rating'),
            'categories' => Category::orderBy('name')->get()
        ]);
    }

    public function showStoryNewChapter(Request $request)
    {
        // Get latest chapter information using a subquery
        $latestChapters = DB::table('chapters')
            ->select(
                'story_id',
                DB::raw('MAX(COALESCE(scheduled_publish_at, created_at)) as latest_chapter_time')
            )
            ->where('status', 'published')
            ->groupBy('story_id');

        // Use withSubquery to avoid GROUP BY issues
        $storiesQuery = Story::select('stories.*')
            ->where('stories.status', 'published')
            ->withAvg('ratings as average_rating', 'rating')
            ->with(['latestChapter' => function ($query) {
                $query->select('id', 'story_id', 'number', 'slug', 'created_at')
                    ->where('status', 'published');
            }])
            ->joinSub($latestChapters, 'latest_chapters', function ($join) {
                $join->on('stories.id', '=', 'latest_chapters.story_id');
            })
            ->orderByDesc('average_rating')
            ->orderByDesc('latest_chapters.latest_chapter_time');

        // Apply advanced search filters
        $storiesQuery = $this->applyAdvancedFilters($storiesQuery, $request);

        $stories = $storiesQuery->paginate(20);

        return view('pages.search.results', [
            'stories' => $stories,
            'query' => 'new-chapter',
            'isSearch' => false,
            'searchUrl' => route('story.new.chapter'),
            'categories' => Category::orderBy('name')->get()
        ]);
    }

    public function showStoryNew(Request $request)
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
                'completed',
                'reviewed_at',
                'cover_medium',
                'author_name',
                'description'
            ])
            ->withCount(['chapters' => function ($query) {
                $query->where('status', 'published');
            }])
            ->whereHas('chapters', function ($query) {
                $query->where('status', 'published');
            })
            ->whereMonth('reviewed_at', now()->month)
            ->whereYear('reviewed_at', now()->year)
            ->orderByDesc('reviewed_at');

        // Apply advanced search filters
        $query = $this->applyAdvancedFilters($query, $request);

        $stories = $query->paginate(20);

        return view('pages.search.results', [
            'stories' => $stories,
            'query' => 'new',
            'isSearch' => false,
            'searchUrl' => route('story.new'),
            'categories' => Category::orderBy('name')->get()
        ]);
    }

    public function showStoryView(Request $request)
    {
        $storyViews = DB::table('chapters')
            ->select('story_id', DB::raw('SUM(views) as total_views'))
            ->where('status', 'published')
            ->groupBy('story_id');

        $storiesQuery = Story::select('stories.*')
            ->with([
                'categories',
                'chapters' => function ($query) {
                    $query->select('id', 'story_id', 'views')
                        ->where('status', 'published');
                },
                'latestChapter' => function ($query) {
                    $query->select('id', 'story_id', 'number', 'created_at')
                        ->where('status', 'published');
                }
            ])
            ->joinSub($storyViews, 'story_views', function ($join) {
                $join->on('stories.id', '=', 'story_views.story_id');
            })
            ->addSelect('story_views.total_views')
            ->orderByDesc('story_views.total_views');

        // Apply advanced search filters
        $storiesQuery = $this->applyAdvancedFilters($storiesQuery, $request);

        $stories = $storiesQuery->paginate(20);

        return view('pages.search.results', [
            'stories' => $stories,
            'query' => 'view',
            'isSearch' => false,
            'searchUrl' => route('story.view'),
            'categories' => Category::orderBy('name')->get()
        ]);
    }

    public function showStoryFollow(Request $request)
    {
        $storiesQuery = Story::withCount('bookmarks')
            ->with([
                'categories',
                'chapters' => function ($query) {
                    $query->select('id', 'story_id', 'views')
                        ->where('status', 'published');
                },
                'latestChapter' => function ($query) {
                    $query->select('id', 'story_id', 'number', 'created_at')
                        ->where('status', 'published');
                }
            ])
            ->orderByDesc('bookmarks_count');

        // Apply advanced search filters
        $storiesQuery = $this->applyAdvancedFilters($storiesQuery, $request);

        $stories = $storiesQuery->paginate(20);

        return view('pages.search.results', [
            'stories' => $stories,
            'query' => 'follow',
            'isSearch' => false,
            'searchUrl' => route('story.follow'),
            'categories' => Category::orderBy('name')->get()
        ]);
    }

    public function showCompletedStories(Request $request)
    {
        $storiesQuery = Story::with([
            'categories',
            'chapters' => function ($query) {
                $query->select('id', 'story_id', 'price', 'is_free')
                    ->where('status', 'published');
            },
            'latestChapter' => function ($query) {
                $query->select('id', 'story_id', 'number', 'created_at')
                    ->where('status', 'published');
            }
        ])
            ->published()
            ->where('completed', true)
            ->whereHas('chapters', function ($query) {
                $query->where('status', 'published');
            })
            ->select([
                'id',
                'title',
                'slug',
                'cover',
                'completed',
                'updated_at',
                'cover_medium',
                'author_name',
                'description',
            ])
            ->withCount(['chapters' => function ($query) {
                $query->where('status', 'published');
            }])
            ->latest('updated_at');

        // Apply advanced search filters
        $storiesQuery = $this->applyAdvancedFilters($storiesQuery, $request);

        $stories = $storiesQuery->paginate(20);

        return view('pages.search.results', [
            'stories' => $stories,
            'query' => 'completed',
            'isSearch' => false,
            'searchUrl' => route('story.completed'),
            'categories' => Category::orderBy('name')->get()
        ]);
    }

    public function index(Request $request)
    {

        $hotStories = $this->getHotStories($request);
        $newStories = $this->getNewStories();
        $ratingStories = $this->getRatingStories();

        $latestUpdatedStories = $this->latestUpdatedStories();

        $topViewedStories = $this->topViewedStories();

        $topFollowedStories = $this->topFollowedStories();

        $completedStories = $this->getCompletedStories();

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

        return view('pages.home', compact(
            'hotStories',
            'newStories',
            'completedStories',
            'ratingStories',
            'latestUpdatedStories',
            'topViewedStories',
            'topFollowedStories',
        ));
    }

    private function getCompletedStories()
    {
        return Story::with([
            'categories',
            'chapters' => function ($query) {
                $query->select('id', 'story_id', 'price', 'is_free')
                    ->where('status', 'published');
            },
            'latestChapter' => function ($query) {
                $query->select('id', 'story_id', 'number', 'created_at')
                    ->where('status', 'published');
            }
        ])
            ->published()
            ->where('completed', true)
            ->whereHas('chapters', function ($query) {
                $query->where('status', 'published');
            })
            ->select([
                'id',
                'title',
                'slug',
                'cover',
                'is_18_plus',
                'completed',
                'updated_at',
                'author_name',
                'has_combo',
                'description',
                'combo_price'
            ])
            ->withCount(['chapters' => function ($query) {
                $query->where('status', 'published');
            }])
            ->latest('updated_at')
            ->get();
    }


    private function getHotStories($request)
    {
        $featuredStories = $this->getFeaturedStories($request);
        return $featuredStories;
    }

    /**
     * Lấy truyện đề cử theo featured_order
     */
    private function getFeaturedStories($request)
    {
        $query = Story::with([
            'chapters' => function ($query) {
                $query->select('id', 'story_id', 'views', 'created_at')
                    ->where('status', 'published');
            },
            'latestChapter' => function ($query) {
                $query->select('id', 'story_id', 'number', 'created_at')
                    ->where('status', 'published');
            }
        ])
            ->published()
            ->where('is_featured', true)
            ->whereHas('chapters', function ($query) {
                $query->where('status', 'published');
            })
            ->select([
                'id',
                'title',
                'slug',
                'cover',
                'completed',
                'is_18_plus',
                'description',
                'created_at',
                'updated_at',
                'author_name',
                'is_featured',
                'featured_order'
            ])
            ->withCount([
                'chapters' => fn($q) => $q->where('status', 'published'),
                'storyPurchases',
                'chapterPurchases',
                'ratings',
                'bookmarks'
            ])
            ->selectSub(function ($q) {
                $q->from('ratings')
                    ->selectRaw('AVG(rating)')
                    ->whereColumn('ratings.story_id', 'stories.id');
            }, 'average_rating');

        if ($request && $request->category_id) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        $featuredStories = $query
            ->orderBy('featured_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->take(12)
            ->get();

        $featuredStories->each(function ($story) {
            $story->hot_score = $this->calculateHotScore($story);
        });

        return $featuredStories;
    }

    /**
     * Lấy truyện hot theo thuật toán tính điểm (fallback khi không có featured)
     */
    private function getHotStoriesByScore($request)
    {
        $query = Story::with([
            'chapters' => function ($query) {
                $query->select('id', 'story_id', 'views', 'created_at')
                    ->where('status', 'published');
            },
            'latestChapter' => function ($query) {
                $query->select('id', 'story_id', 'number', 'created_at')
                    ->where('status', 'published');
            }
        ])
            ->published()
            ->whereHas('chapters', function ($query) {
                $query->where('status', 'published');
            })
            ->select([
                'id',
                'title',
                'slug',
                'cover',
                'completed',
                'is_18_plus',
                'description',
                'created_at',
                'updated_at',
                'author_name'
            ])
            ->withCount([
                'chapters' => fn($q) => $q->where('status', 'published'),
                'storyPurchases',
                'chapterPurchases',
                'ratings',
                'bookmarks'
            ])
            ->selectSub(function ($q) {
                $q->from('ratings')
                    ->selectRaw('AVG(rating)')
                    ->whereColumn('ratings.story_id', 'stories.id');
            }, 'average_rating')
            ->where('updated_at', '>=', now()->subDays(30));

        // Filter by category if requested
        if ($request && $request->category_id) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        // Tính hot score và sắp xếp
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
        return ($story->story_purchases_count * 3) +
            ($story->chapter_purchases_count * 2) +
            ($story->ratings_count * 1.5) +
            ($story->average_rating * 2) +
            ($story->bookmarks_count * 1);
    }

    private function getNewStories()
    {
        $query = Story::with(['latestChapter' => function ($query) {
            $query->select('id', 'story_id', 'title', 'slug', 'number', 'views', 'created_at', 'status')
                ->where('status', 'published');
        }, 'categories', 'user:id,name'])
            ->published()
            ->select([
                'id',
                'user_id',
                'title',
                'slug',
                'cover',
                'status',
                'completed',
                'is_18_plus',
                'reviewed_at',
                'cover_medium',
                'author_name',
                'description'
            ])
            ->withCount(['chapters' => function ($query) {
                $query->where('status', 'published');
            }])
            ->whereHas('chapters', function ($query) {
                $query->where('status', 'published');
            })
            ->whereMonth('reviewed_at', now()->month)
            ->whereYear('reviewed_at', now()->year);

        return $query->orderByDesc('reviewed_at')
            ->take(20)
            ->get();
    }


    public function latestUpdatedStories()
    {
        // Get latest chapter information using a subquery
        $latestChapters = DB::table('chapters')
            ->select(
                'story_id',
                DB::raw('MAX(COALESCE(scheduled_publish_at, created_at)) as latest_chapter_time'),
                DB::raw('COUNT(*) as chapters_count')
            )
            ->where('status', 'published')
            ->groupBy('story_id');

        // Use withSubquery to avoid GROUP BY issues
        return Story::select(
            'stories.id',
            'stories.title',
            'stories.user_id',
            'stories.status',
            'stories.reviewed_at',
            'stories.slug',
            'stories.is_18_plus',
            'stories.created_at',
            'stories.updated_at',
            'stories.cover',
            'stories.cover_medium',
            'stories.cover_thumbnail',
            'stories.author_name',
        )
            ->where('stories.status', 'published')
            ->withAvg('ratings as average_rating', 'rating')
            ->with(['latestChapter' => function ($query) {
                $query->select('id', 'story_id', 'number', 'slug', 'created_at')
                    ->where('status', 'published');
            }])
            ->joinSub($latestChapters, 'latest_chapters', function ($join) {
                $join->on('stories.id', '=', 'latest_chapters.story_id');
            })
            ->addSelect('latest_chapters.chapters_count', 'latest_chapters.latest_chapter_time')
            ->orderByDesc('average_rating')
            ->orderByDesc('latest_chapters.latest_chapter_time')
            ->limit(10)
            ->get();
    }

    public function getRatingStories()
    {
        return Story::select(
            'stories.id',
            'stories.user_id',
            'stories.title',
            'stories.slug',
            'stories.description',
            'stories.cover',
            'stories.cover_medium',
            'stories.cover_thumbnail',
            'stories.completed',
            'stories.link_aff',
            'stories.story_type',
            'stories.author_name',
            'stories.is_18_plus',
            'stories.combo_price',
            'stories.has_combo',
            'stories.translator_name',
            'stories.is_monopoly',
            'stories.submitted_at',
            'stories.review_note',
            'stories.admin_note',
            'stories.created_at',
            'stories.updated_at',
            'stories.reviewed_at'
        )
            ->where('stories.status', 'published')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('ratings')
                    ->whereColumn('ratings.story_id', 'stories.id');
            })
            ->withAvg('ratings as average_rating', 'rating')
            ->with(['chapters' => function ($query) {
                $query->select('id', 'story_id', 'views')
                    ->where('status', 'published');
            }])
            ->orderByDesc('average_rating')
            ->orderByRaw('COALESCE(stories.reviewed_at, stories.created_at) ASC')
            ->limit(10)
            ->get();
    }


    public function topViewedStories()
    {
        $storyViews = DB::table('chapters')
            ->select('story_id', DB::raw('SUM(views) as total_views'))
            ->where('status', 'published')
            ->groupBy('story_id')
            ->having('total_views', '>', 0);

        $stories = Story::select('stories.*')
            ->where('stories.status', 'published')
            ->with(['chapters' => function ($query) {
                $query->select('id', 'story_id', 'views')
                    ->where('status', 'published');
            }])
            ->joinSub($storyViews, 'story_views', function ($join) {
                $join->on('stories.id', '=', 'story_views.story_id');
            })
            ->addSelect('story_views.total_views')
            ->orderByDesc('story_views.total_views')
            ->limit(10)
            ->get();

        return $stories;
    }

    public function topFollowedStories()
    {
        $stories = Story::withCount('bookmarks')
            ->where('status', 'published')
            ->with(['chapters' => function ($query) {
                $query->select('id', 'story_id', 'views')
                    ->where('status', 'published');
            }])
            ->having('bookmarks_count', '>', 0)
            ->orderByDesc('bookmarks_count')
            ->limit(10)
            ->get();
        return $stories;
    }

    public function showStory(Request $request, $slug)
    {
        $cacheKey = "story_{$slug}";
        $cachedStory = \App\Http\Middleware\AffiliateRedirect::getCachedEntity($cacheKey);
        
        if ($cachedStory && $cachedStory->status === 'published') {
            $story = $cachedStory;
        } else {
            $story = Story::where('slug', $slug)
                ->published()
                ->with(['categories' => function ($query) {
                    $query->select('categories.id', 'categories.name', 'categories.slug');
                }])
                ->firstOrFail();
        }

        $story->load(['chapters' => function ($query) {
            $query->where('status', 'published')->select('id', 'story_id', 'views');
        }]);

        $chapters = Chapter::where('story_id', $story->id)
            ->published()
            ->with(['purchases' => function ($query) {
                if (auth()->check()) {
                    $query->where('user_id', auth()->id());
                } else {
                    $query->whereRaw('1 = 0');
                }
            }])
            ->orderBy('number', 'desc')
            ->paginate(20);

        $chapterPurchaseStatus = [];
        if (auth()->check()) {
            $chapterIds = $chapters->pluck('id');
            $purchasedChapterIds = \App\Models\ChapterPurchase::whereIn('chapter_id', $chapterIds)
                ->where('user_id', auth()->id())
                ->pluck('chapter_id')
                ->toArray();
            
            foreach ($chapterIds as $chapterId) {
                $chapterPurchaseStatus[$chapterId] = in_array($chapterId, $purchasedChapterIds);
            }
        }

        $publishedChapters = $story->chapters;
        $stats = [
            'total_chapters' => $publishedChapters->count(),
            'total_views' => $publishedChapters->sum('views'),
            'total_bookmarks' => $story->bookmarks()->count(),
            'ratings' => [
                'count' => Rating::where('story_id', $story->id)->count(),
                'average' => Rating::where('story_id', $story->id)->avg('rating') ?? 0
            ]
        ];

        $status = (object)[
            'status' => $story->completed ? 'done' : 'ongoing'
        ];

        $storyCategories = $story->categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ];
        });

        $chapters = $story->chapters()
            ->published()
            ->orderBy('number', 'asc')
            ->paginate(50);

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

        $featuredStories = $this->getFeaturedStories($request);

        $authorStories = collect();
        if ($story->author_name) {
            $authorStories = Story::query()
                ->published()
                ->where('author_name', 'LIKE', "%{$story->author_name}%")
                ->where('id', '!=', $story->id)
                ->with(['categories', 'chapters'])
                ->take(5)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $translatorStories = collect();
        if ($story->user_id) {
            $translatorStories = Story::query()
                ->published()
                ->where('user_id', $story->user_id)
                ->where('id', '!=', $story->id)
                ->with(['categories', 'chapters', 'user'])
                ->take(5)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('pages.story', compact(
            'story',
            'stats',
            'status',
            'chapters',
            'pinnedComments',
            'regularComments',
            'storyCategories',
            'featuredStories',
            'authorStories',
            'translatorStories',
            'chapterPurchaseStatus'
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

        $chapterPurchaseStatus = [];
        if (auth()->check()) {
            $chapterIds = $chapters->pluck('id');
            $purchasedChapterIds = \App\Models\ChapterPurchase::whereIn('chapter_id', $chapterIds)
                ->where('user_id', auth()->id())
                ->pluck('chapter_id')
                ->toArray();
            
            foreach ($chapterIds as $chapterId) {
                $chapterPurchaseStatus[$chapterId] = in_array($chapterId, $purchasedChapterIds);
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'html' => view('components.chapter-items', [
                    'chapters' => $chapters,
                    'story' => $story,
                    'sortOrder' => $sortOrder,
                    'chapterPurchaseStatus' => $chapterPurchaseStatus
                ])->render(),
                'pagination' => view('components.pagination', ['paginator' => $chapters])->render()
            ]);
        }

        return redirect()->route('show.page.story', $story->slug);
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

            if (
                in_array($user->role, ['admin', 'mod']) ||
                ($user->role == 'author' && $story->user_id == $user->id)
            ) {
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

    /**
     * Apply advanced search filters to the query
     */
    private function applyAdvancedFilters($query, Request $request)
    {
        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category);
            });
        }

        // Filter by completion status
        if ($request->filled('status')) {
            if ($request->status === 'completed') {
                $query->where('completed', true);
            } elseif ($request->status === 'ongoing') {
                $query->where('completed', false);
            }
        }

        // Filter by number of chapters
        if ($request->filled('chapters')) {
            $chaptersFilter = $request->chapters;
            
            // Add chapters count if not already added
            if (!$query->getQuery()->columns || !in_array('chapters_count', $query->getQuery()->columns)) {
                $query->withCount(['chapters' => function ($q) {
                    $q->where('status', 'published');
                }]);
            }

            switch ($chaptersFilter) {
                case '1-10':
                    $query->having('chapters_count', '>=', 1)->having('chapters_count', '<=', 10);
                    break;
                case '11-50':
                    $query->having('chapters_count', '>=', 11)->having('chapters_count', '<=', 50);
                    break;
                case '51-100':
                    $query->having('chapters_count', '>=', 51)->having('chapters_count', '<=', 100);
                    break;
                case '100+':
                    $query->having('chapters_count', '>', 100);
                    break;
            }
        }

        // Apply sorting
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'most_chapters':
                    $query->withCount(['chapters' => function ($q) {
                        $q->where('status', 'published');
                    }])->orderBy('chapters_count', 'desc');
                    break;
                case 'least_chapters':
                    $query->withCount(['chapters' => function ($q) {
                        $q->where('status', 'published');
                    }])->orderBy('chapters_count', 'asc');
                    break;
                case 'most_views':
                    $query->withCount(['chapters' => function ($q) {
                        $q->where('status', 'published');
                    }])->withSum('chapters', 'views')->orderBy('chapters_sum_views', 'desc');
                    break;
                case 'highest_rating':
                    $query->withAvg('ratings', 'rating')->orderBy('ratings_avg_rating', 'desc');
                    break;
            }
        }

        return $query;
    }
}
