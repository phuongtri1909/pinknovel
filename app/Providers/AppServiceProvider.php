<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Donate;
use App\Models\Rating;
use App\Models\Social;
use App\Models\Status;
use App\Models\Chapter;
use App\Models\Socials;
use App\Models\Category;
use App\Models\Story;
use Carbon\Carbon;
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
                    'categories.description',
                    DB::raw('COUNT(DISTINCT stories.id) as stories_count'),
                    DB::raw('SUM(chapters.views) as total_views'),
                    DB::raw('AVG(ratings.rating) as avg_rating'),
                    // Calculate a "hotness" score combining views and rating
                    DB::raw('SUM(chapters.views) * AVG(COALESCE(ratings.rating, 3)) as hotness_score')
                ])
                ->join('category_story', 'categories.id', '=', 'category_story.category_id')
                ->join('stories', 'category_story.story_id', '=', 'stories.id')
                ->join('chapters', 'stories.id', '=', 'chapters.story_id')
                ->leftJoin('ratings', 'stories.id', '=', 'ratings.story_id')
                ->groupBy([
                    'categories.id',
                    'categories.name',
                    'categories.slug',
                    'categories.description'
                ])
                ->orderByDesc('hotness_score')
                ->take(20)
                ->get();
                
            $view->with('topCategories', $topCategories);
            
            // Get top 10 hot stories for today
            $dailyHotStories = Story::select([
                'stories.id',
                'stories.title',
                'stories.slug',
                'stories.description',
                'stories.cover',
                DB::raw('COUNT(chapters.id) as chapters_count'),
                DB::raw('SUM(chapters.views) as total_views'),
                DB::raw('AVG(daily_ratings.rating) as daily_rating'),
                DB::raw('(SUM(chapters.views) * AVG(COALESCE(daily_ratings.rating, 3))) as hotness_score')
            ])
            ->with(['latestChapter'])
            ->join('chapters', 'stories.id', '=', 'chapters.story_id')
            ->leftJoin(DB::raw('(SELECT story_id, rating FROM ratings WHERE DATE(created_at) = CURRENT_DATE()) as daily_ratings'), 
                'stories.id', '=', 'daily_ratings.story_id')
            ->where('stories.status', 'published')
            ->groupBy('stories.id', 'stories.title', 'stories.slug', 'stories.description', 'stories.cover')
            ->orderByDesc('hotness_score')
            ->take(10)
            ->get();
            
            // Get top 10 hot stories for this week
            $weeklyHotStories = Story::select([
                'stories.id',
                'stories.title',
                'stories.slug',
                'stories.description',
                'stories.cover',
                DB::raw('COUNT(chapters.id) as chapters_count'),
                DB::raw('SUM(chapters.views) as total_views'),
                DB::raw('AVG(weekly_ratings.rating) as weekly_rating'),
                DB::raw('(SUM(chapters.views) * AVG(COALESCE(weekly_ratings.rating, 3))) as hotness_score')
            ])
            ->with(['latestChapter'])
            ->join('chapters', 'stories.id', '=', 'chapters.story_id')
            ->leftJoin(DB::raw('(SELECT story_id, rating FROM ratings WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)) as weekly_ratings'), 
                'stories.id', '=', 'weekly_ratings.story_id')
            ->where('stories.status', 'published')
            ->groupBy('stories.id', 'stories.title', 'stories.slug', 'stories.description', 'stories.cover')
            ->orderByDesc('hotness_score')
            ->take(10)
            ->get();
            
            // Get top 10 hot stories for this month
            $monthlyHotStories = Story::select([
                'stories.id',
                'stories.title',
                'stories.slug',
                'stories.description',
                'stories.cover',
                DB::raw('COUNT(chapters.id) as chapters_count'),
                DB::raw('SUM(chapters.views) as total_views'),
                DB::raw('AVG(monthly_ratings.rating) as monthly_rating'),
                DB::raw('(SUM(chapters.views) * AVG(COALESCE(monthly_ratings.rating, 3))) as hotness_score')
            ])
            ->with(['latestChapter'])
            ->join('chapters', 'stories.id', '=', 'chapters.story_id')
            ->leftJoin(DB::raw('(SELECT story_id, rating FROM ratings WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)) as monthly_ratings'), 
                'stories.id', '=', 'monthly_ratings.story_id')
            ->where('stories.status', 'published')
            ->groupBy('stories.id', 'stories.title', 'stories.slug', 'stories.description', 'stories.cover')
            ->orderByDesc('hotness_score')
            ->take(10)
            ->get();

            $view->with('dailyHotStories', $dailyHotStories);
            $view->with('weeklyHotStories', $weeklyHotStories);
            $view->with('monthlyHotStories', $monthlyHotStories);

            $donate = Donate::first() ?? new Donate();
            $view->with('donate', $donate);
        });
    }
}