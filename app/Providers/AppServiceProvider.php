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
            'pages.chapter',
            'pages.information.author.author_create',
            'pages.information.author.author_edit',
        ], function ($view) {
            // Get all categories for standard navigation
            $allCategories = Category::withCount('stories')->orderBy('name')->get();
            $view->with('categories', $allCategories);


            $dailyTopPurchased = $this->getTopStoriesPurchased(Carbon::today());

            $weeklyTopPurchased = $this->getTopStoriesPurchased(Carbon::today()->subDays(7));

            $monthlyTopPurchased = $this->getTopStoriesPurchased(Carbon::today()->subDays(30));


            $banners = Banner::active()->get();

            $view->with('dailyTopPurchased', $dailyTopPurchased);
            $view->with('weeklyTopPurchased', $weeklyTopPurchased);
            $view->with('monthlyTopPurchased', $monthlyTopPurchased);
            $view->with('banners', $banners);

            $donate = Donate::first() ?? new Donate();
            $view->with('donate', $donate);
        });
    }

    private function getTopStoriesPurchased(Carbon $fromDate)
    {
        // Purchases từ bảng story_purchases
        $storyPurchases = DB::table('story_purchases')
            ->select('story_id', DB::raw('COUNT(*) as purchase_count'), DB::raw('MAX(created_at) as latest'))
            ->where('created_at', '>=', $fromDate)
            ->groupBy('story_id');

        // Purchases từ chapter_purchases
        $chapterPurchases = DB::table('chapter_purchases')
            ->join('chapters', 'chapter_purchases.chapter_id', '=', 'chapters.id')
            ->select('chapters.story_id', DB::raw('COUNT(*) as purchase_count'), DB::raw('MAX(chapter_purchases.created_at) as latest'))
            ->where('chapter_purchases.created_at', '>=', $fromDate)
            ->groupBy('chapters.story_id');

        // Gom 2 bảng bằng UNION
        $merged = $storyPurchases->unionAll($chapterPurchases);

        // Tính tổng purchase và latest time
        $totals = DB::table(DB::raw("({$merged->toSql()}) as purchases"))
            ->mergeBindings($merged)
            ->select('story_id', DB::raw('SUM(purchase_count) as total_purchases'), DB::raw('MAX(latest) as latest_purchase_at'))
            ->groupBy('story_id')
            ->orderByDesc('total_purchases')
            ->limit(10)
            ->get();

        // Lấy danh sách truyện tương ứng
        $storyIds = $totals->pluck('story_id')->toArray();
        $stories = Story::whereIn('id', $storyIds)->where('status', 'published')->get()->keyBy('id');

        // Gộp dữ liệu purchase vào model Story
        return $totals->map(function ($row) use ($stories) {
            $story = $stories[$row->story_id] ?? null;
            if (!$story) return null;

            $story->total_purchases = $row->total_purchases;
            $story->latest_purchase_at = $row->latest_purchase_at;
            $story->latest_purchase_diff = $row->latest_purchase_at
                ? Carbon::parse($row->latest_purchase_at)->diffForHumans()
                : 'Chưa có ai mua';

            return $story;
        })->filter();
    }
}
