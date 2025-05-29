<?php

namespace App\Providers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Story;
use App\Models\Banner;
use App\Models\Donate;
use App\Models\Rating;
use App\Models\Social;
use App\Models\Status;
use App\Models\Chapter;
use App\Models\Socials;
use App\Models\Category;
use App\Models\StoryPurchase;
use App\Models\ChapterPurchase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        View::composer([
            'layouts.partials.header',
            'pages.home',
            'pages.search.results',
            'layouts.partials.footer',
            'pages.chapter'
        ], function ($view) {
            // Get all categories for standard navigation
            $allCategories = Category::withCount('stories')->orderBy('name')->get();
            $view->with('categories', $allCategories);

            // Get top 20 categories with the hottest stories (based on total chapter views and rating)
            $topCategories = Category::select('categories.id', 'categories.name', 'categories.slug', 'categories.description')
                ->withCount('stories')
                ->addSelect([
                    'total_views' => Chapter::selectRaw('SUM(views)')
                        ->join('stories', 'chapters.story_id', '=', 'stories.id')
                        ->join('category_story', 'stories.id', '=', 'category_story.story_id')
                        ->whereColumn('category_story.category_id', 'categories.id')
                        ->where('chapters.status', 'published')
                        ->limit(1),
                    'avg_rating' => Rating::selectRaw('AVG(rating)')
                        ->join('stories', 'ratings.story_id', '=', 'stories.id')
                        ->join('category_story', 'stories.id', '=', 'category_story.story_id')
                        ->whereColumn('category_story.category_id', 'categories.id')
                        ->limit(1)
                ])
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('category_story')
                        ->join('stories', 'category_story.story_id', '=', 'stories.id')
                        ->join('chapters', 'stories.id', '=', 'chapters.story_id')
                        ->whereColumn('category_story.category_id', 'categories.id');
                })
                ->orderByRaw('COALESCE(total_views, 0) * COALESCE(avg_rating, 3) DESC')
                ->take(20)
                ->get();

            $view->with('topCategories', $topCategories);

            // Get top 10 hot stories for today
            $dailyTopPurchased = DB::table('stories as main_table')
                ->select(
                    'main_table.id',
                    'main_table.title',
                    'main_table.slug',
                    'main_table.description',
                    'main_table.cover'
                )
                ->where('main_table.status', 'published')
                ->selectSub(function ($query) {
                    $query->selectRaw('COUNT(*)')
                        ->from('story_purchases')
                        ->whereColumn('story_purchases.story_id', 'main_table.id')
                        ->whereRaw('DATE(story_purchases.created_at) = CURRENT_DATE()');
                }, 'story_purchases_count')
                ->selectSub(function ($query) {
                    $query->selectRaw('COUNT(*)')
                        ->from('chapter_purchases')
                        ->join('chapters', 'chapter_purchases.chapter_id', '=', 'chapters.id')
                        ->whereColumn('chapters.story_id', 'main_table.id')
                        ->whereRaw('DATE(chapter_purchases.created_at) = CURRENT_DATE()');
                }, 'chapter_purchases_count')
                ->selectSub(function ($query) {
                    $query->selectRaw('MAX(purchase_time)')
                        ->from(function ($subQuery) {
                            $subQuery->select(DB::raw('story_purchases.created_at as purchase_time'))
                                ->from('story_purchases')
                                ->whereColumn('story_purchases.story_id', 'main_table.id')
                                ->whereRaw('DATE(story_purchases.created_at) = CURRENT_DATE()')
                                ->union(
                                    DB::table('chapter_purchases')
                                        ->select('chapter_purchases.created_at')
                                        ->join('chapters', 'chapter_purchases.chapter_id', '=', 'chapters.id')
                                        ->whereColumn('chapters.story_id', 'main_table.id')
                                        ->whereRaw('DATE(chapter_purchases.created_at) = CURRENT_DATE()')
                                );
                        }, 'combined_times');
                }, 'latest_purchase_at')
                ->where(function ($query) {
                    $query->whereExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('story_purchases')
                            ->whereColumn('story_purchases.story_id', 'main_table.id')
                            ->whereRaw('DATE(story_purchases.created_at) = CURRENT_DATE()');
                    })
                        ->orWhereExists(function ($sub) {
                            $sub->select(DB::raw(1))
                                ->from('chapter_purchases')
                                ->join('chapters', 'chapter_purchases.chapter_id', '=', 'chapters.id')
                                ->whereColumn('chapters.story_id', 'main_table.id')
                                ->whereRaw('DATE(chapter_purchases.created_at) = CURRENT_DATE()');
                        });
                })
                ->orderByRaw('(COALESCE(story_purchases_count, 0) + COALESCE(chapter_purchases_count, 0)) DESC')
                ->limit(10)
                ->get();


            // Convert to Story models and add necessary relationships
            $dailyTopPurchased = collect($dailyTopPurchased)->map(function ($item) {
                $story = Story::find($item->id);
                $story->total_purchases = $item->story_purchases_count + $item->chapter_purchases_count;
                $story->latest_purchase_diff = $item->latest_purchase_at
                    ? Carbon::parse($item->latest_purchase_at)->diffForHumans()
                    : 'Chưa có ai mua';
                return $story;
            });

            // ==== HOT TUẦN ====
            $weeklyTopPurchased = DB::table('stories')
                ->select(
                    'stories.id',
                    'stories.title',
                    'stories.slug',
                    'stories.description',
                    'stories.cover'
                )
                ->where('stories.status', 'published')
                ->selectSub(function ($query) {
                    $query->selectRaw('COUNT(*)')
                        ->from('story_purchases')
                        ->whereColumn('story_purchases.story_id', 'stories.id')
                        ->whereRaw('story_purchases.created_at >= CURDATE() - INTERVAL 7 DAY');
                }, 'story_purchases_count')
                ->selectSub(function ($query) {
                    $query->selectRaw('COUNT(*)')
                        ->from('chapter_purchases')
                        ->join('chapters', 'chapter_purchases.chapter_id', '=', 'chapters.id')
                        ->whereColumn('chapters.story_id', 'stories.id')
                        ->whereRaw('chapter_purchases.created_at >= CURDATE() - INTERVAL 7 DAY');
                }, 'chapter_purchases_count')
                ->selectSub(function ($query) {
                    $query->selectRaw('MAX(purchase_time)')
                        ->from(function ($subQuery) {
                            $subQuery->select(DB::raw('story_purchases.created_at as purchase_time'))
                                ->from('story_purchases')
                                ->whereColumn('story_purchases.story_id', 'stories.id')
                                ->whereRaw('story_purchases.created_at >= CURDATE() - INTERVAL 7 DAY')
                                ->union(
                                    DB::table('chapter_purchases')
                                        ->select('chapter_purchases.created_at')
                                        ->join('chapters', 'chapter_purchases.chapter_id', '=', 'chapters.id')
                                        ->whereColumn('chapters.story_id', 'stories.id')
                                        ->whereRaw('chapter_purchases.created_at >= CURDATE() - INTERVAL 7 DAY')
                                );
                        }, 'combined_times');
                }, 'latest_purchase_at')
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('story_purchases')
                        ->whereColumn('story_purchases.story_id', 'stories.id')
                        ->whereRaw('story_purchases.created_at >= CURDATE() - INTERVAL 7 DAY');
                })
                ->orWhereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('chapter_purchases')
                        ->join('chapters', 'chapter_purchases.chapter_id', '=', 'chapters.id')
                        ->whereColumn('chapters.story_id', 'stories.id')
                        ->whereRaw('chapter_purchases.created_at >= CURDATE() - INTERVAL 7 DAY');
                })
                ->orderByRaw('(COALESCE(story_purchases_count, 0) + COALESCE(chapter_purchases_count, 0)) DESC')
                ->limit(10)
                ->get();

            // Convert to Story models and add necessary relationships
            $weeklyTopPurchased = collect($weeklyTopPurchased)->map(function ($item) {
                $story = Story::find($item->id);
                $story->total_purchases = $item->story_purchases_count + $item->chapter_purchases_count;
                $story->latest_purchase_diff = $item->latest_purchase_at
                    ? Carbon::parse($item->latest_purchase_at)->diffForHumans()
                    : 'Chưa có ai mua';
                return $story;
            });

            // ==== HOT THÁNG ====
            $monthlyTopPurchased = DB::table('stories')
                ->select(
                    'stories.id',
                    'stories.title',
                    'stories.slug',
                    'stories.description',
                    'stories.cover'
                )
                ->where('stories.status', 'published')
                ->selectSub(function ($query) {
                    $query->selectRaw('COUNT(*)')
                        ->from('story_purchases')
                        ->whereColumn('story_purchases.story_id', 'stories.id')
                        ->whereRaw('story_purchases.created_at >= CURDATE() - INTERVAL 30 DAY');
                }, 'story_purchases_count')
                ->selectSub(function ($query) {
                    $query->selectRaw('COUNT(*)')
                        ->from('chapter_purchases')
                        ->join('chapters', 'chapter_purchases.chapter_id', '=', 'chapters.id')
                        ->whereColumn('chapters.story_id', 'stories.id')
                        ->whereRaw('chapter_purchases.created_at >= CURDATE() - INTERVAL 30 DAY');
                }, 'chapter_purchases_count')
                ->selectSub(function ($query) {
                    $query->selectRaw('MAX(purchase_time)')
                        ->from(function ($subQuery) {
                            $subQuery->select(DB::raw('story_purchases.created_at as purchase_time'))
                                ->from('story_purchases')
                                ->whereColumn('story_purchases.story_id', 'stories.id')
                                ->whereRaw('story_purchases.created_at >= CURDATE() - INTERVAL 30 DAY')
                                ->union(
                                    DB::table('chapter_purchases')
                                        ->select('chapter_purchases.created_at')
                                        ->join('chapters', 'chapter_purchases.chapter_id', '=', 'chapters.id')
                                        ->whereColumn('chapters.story_id', 'stories.id')
                                        ->whereRaw('chapter_purchases.created_at >= CURDATE() - INTERVAL 30 DAY')
                                );
                        }, 'combined_times');
                }, 'latest_purchase_at')
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('story_purchases')
                        ->whereColumn('story_purchases.story_id', 'stories.id')
                        ->whereRaw('story_purchases.created_at >= CURDATE() - INTERVAL 30 DAY');
                })
                ->orWhereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('chapter_purchases')
                        ->join('chapters', 'chapter_purchases.chapter_id', '=', 'chapters.id')
                        ->whereColumn('chapters.story_id', 'stories.id')
                        ->whereRaw('chapter_purchases.created_at >= CURDATE() - INTERVAL 30 DAY');
                })
                ->orderByRaw('(COALESCE(story_purchases_count, 0) + COALESCE(chapter_purchases_count, 0)) DESC')
                ->limit(10)
                ->get();

            // Convert to Story models and add necessary relationships
            $monthlyTopPurchased = collect($monthlyTopPurchased)->map(function ($item) {
                $story = Story::find($item->id);
                $story->total_purchases = $item->story_purchases_count + $item->chapter_purchases_count;
                $story->latest_purchase_diff = $item->latest_purchase_at
                    ? Carbon::parse($item->latest_purchase_at)->diffForHumans()
                    : 'Chưa có ai mua';
                return $story;
            });

            $banners = Banner::active()->get();

            $view->with('dailyTopPurchased', $dailyTopPurchased);
            $view->with('weeklyTopPurchased', $weeklyTopPurchased);
            $view->with('monthlyTopPurchased', $monthlyTopPurchased);
            $view->with('banners', $banners);

            $donate = Donate::first() ?? new Donate();
            $view->with('donate', $donate);
        });
    }
}
