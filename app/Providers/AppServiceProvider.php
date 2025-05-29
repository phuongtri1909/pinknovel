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
            $topCategories = Category::select([
                'categories.id',
                'categories.name',
                'categories.slug',
                'categories.description'
            ])
                ->leftJoin('category_story', 'categories.id', '=', 'category_story.category_id')
                ->leftJoin('stories', 'category_story.story_id', '=', 'stories.id')
                ->leftJoin('chapters', 'stories.id', '=', 'chapters.story_id')
                ->leftJoin('ratings', 'stories.id', '=', 'ratings.story_id')
                ->where('stories.status', '=', 'published')
                ->groupBy([
                    'categories.id',
                    'categories.name', 
                    'categories.slug',
                    'categories.description'
                ])
                ->selectRaw('COUNT(DISTINCT stories.id) as stories_count')
                ->selectRaw('SUM(COALESCE(chapters.views, 0)) as total_views')
                ->selectRaw('AVG(COALESCE(ratings.rating, 3)) as avg_rating')
                ->selectRaw('SUM(COALESCE(chapters.views, 0)) * AVG(COALESCE(ratings.rating, 3)) as hotness_score')
                ->orderByDesc('hotness_score')
                ->take(20)
                ->get();

            $view->with('topCategories', $topCategories);

            // Get top 10 hot stories for today
            $dailyTopPurchased = Story::from('stories')
    ->select([
        'stories.id',
        'stories.title',
        'stories.slug',
        'stories.description',
        'stories.cover'
    ])
            ->where('stories.status', 'published')
            ->selectSub(function($query) {
                $query->selectRaw('
                    (SELECT COUNT(*) FROM story_purchases 
                    WHERE story_purchases.story_id = stories.id
                    AND DATE(story_purchases.created_at) = CURRENT_DATE())
                    +
                    (SELECT COUNT(*) FROM chapter_purchases 
                    JOIN chapters ON chapter_purchases.chapter_id = chapters.id
                    WHERE chapters.story_id = stories.id
                    AND DATE(chapter_purchases.created_at) = CURRENT_DATE())'
                );
            }, 'total_purchases')
            ->selectSub(function($query) {
                $query->selectRaw('
                    (SELECT MAX(latest_time) FROM (
                        SELECT MAX(story_purchases.created_at) as latest_time
                        FROM story_purchases
                        WHERE story_purchases.story_id = stories.id
                        AND DATE(story_purchases.created_at) = CURRENT_DATE()
                        UNION
                        SELECT MAX(chapter_purchases.created_at) as latest_time
                        FROM chapter_purchases
                        JOIN chapters ON chapter_purchases.chapter_id = chapters.id
                        WHERE chapters.story_id = stories.id
                        AND DATE(chapter_purchases.created_at) = CURRENT_DATE()
                    ) as merged_times)'
                );
            }, 'latest_purchase_at')
            ->havingRaw('total_purchases > 0')
            ->orderByDesc('total_purchases')
            ->limit(10)
            ->get()
            ->map(function ($story) {
                $story->latest_purchase_diff = $story->latest_purchase_at
                    ? Carbon::parse($story->latest_purchase_at)->diffForHumans()
                    : 'Chưa có ai mua';
                return $story;
            });

            // ==== HOT TUẦN ====
            $weeklyTopPurchased = Story::from('stories')
    ->select([
        'stories.id',
        'stories.title',
        'stories.slug',
        'stories.description',
        'stories.cover'
    ])
            ->where('stories.status', 'published')
            ->selectSub(function($query) {
                $query->selectRaw('
                    (SELECT COUNT(*) FROM story_purchases 
                    WHERE story_purchases.story_id = stories.id
                    AND story_purchases.created_at >= CURDATE() - INTERVAL 7 DAY)
                    +
                    (SELECT COUNT(*) FROM chapter_purchases 
                    JOIN chapters ON chapter_purchases.chapter_id = chapters.id
                    WHERE chapters.story_id = stories.id
                    AND chapter_purchases.created_at >= CURDATE() - INTERVAL 7 DAY)'
                );
            }, 'total_purchases')
            ->selectSub(function($query) {
                $query->selectRaw('
                    (SELECT MAX(latest_time) FROM (
                        SELECT MAX(story_purchases.created_at) as latest_time
                        FROM story_purchases
                        WHERE story_purchases.story_id = stories.id
                        AND story_purchases.created_at >= CURDATE() - INTERVAL 7 DAY
                        UNION
                        SELECT MAX(chapter_purchases.created_at) as latest_time
                        FROM chapter_purchases
                        JOIN chapters ON chapter_purchases.chapter_id = chapters.id
                        WHERE chapters.story_id = stories.id
                        AND chapter_purchases.created_at >= CURDATE() - INTERVAL 7 DAY
                    ) as merged_times)'
                );
            }, 'latest_purchase_at')
            ->havingRaw('total_purchases > 0')
            ->orderByDesc('total_purchases')
            ->limit(10)
            ->get()
            ->map(function ($story) {
                $story->latest_purchase_diff = $story->latest_purchase_at
                    ? Carbon::parse($story->latest_purchase_at)->diffForHumans()
                    : 'Chưa có ai mua';
                return $story;
            });

            // ==== HOT THÁNG ====
            $monthlyTopPurchased = Story::from('stories')
    ->select([
        'stories.id',
        'stories.title',
        'stories.slug',
        'stories.description',
        'stories.cover'
    ])
            ->where('stories.status', 'published')
            ->selectSub(function($query) {
                $query->selectRaw('
                    (SELECT COUNT(*) FROM story_purchases 
                    WHERE story_purchases.story_id = stories.id
                    AND story_purchases.created_at >= CURDATE() - INTERVAL 30 DAY)
                    +
                    (SELECT COUNT(*) FROM chapter_purchases 
                    JOIN chapters ON chapter_purchases.chapter_id = chapters.id
                    WHERE chapters.story_id = stories.id
                    AND chapter_purchases.created_at >= CURDATE() - INTERVAL 30 DAY)'
                );
            }, 'total_purchases')
            ->selectSub(function($query) {
                $query->selectRaw('
                    (SELECT MAX(latest_time) FROM (
                        SELECT MAX(story_purchases.created_at) as latest_time
                        FROM story_purchases
                        WHERE story_purchases.story_id = stories.id
                        AND story_purchases.created_at >= CURDATE() - INTERVAL 30 DAY
                        UNION
                        SELECT MAX(chapter_purchases.created_at) as latest_time
                        FROM chapter_purchases
                        JOIN chapters ON chapter_purchases.chapter_id = chapters.id
                        WHERE chapters.story_id = stories.id
                        AND chapter_purchases.created_at >= CURDATE() - INTERVAL 30 DAY
                    ) as merged_times)'
                );
            }, 'latest_purchase_at')
            ->havingRaw('total_purchases > 0')
            ->orderByDesc('total_purchases')
            ->limit(10)
            ->get()
            ->map(function ($story) {
                $story->latest_purchase_diff = $story->latest_purchase_at
                    ? Carbon::parse($story->latest_purchase_at)->diffForHumans()
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
