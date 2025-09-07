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
     * Static variables để tránh duplicate queries trong cùng request
     */
    private static $categories = null;
    private static $banners = null;
    private static $donate = null;

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

        // Composer cho categories - chỉ load khi cần
        View::composer([
            'layouts.partials.header',
            'pages.home',
            'pages.search.results',
            'layouts.partials.footer',
            'pages.chapter',
            'pages.information.author.author_create',
            'pages.information.author.author_edit',
        ], function ($view) {
            $view->with('categories', $this->getCategories());
        });

        // Composer cho top purchased stories - load cho trang home và chapter
        View::composer(['pages.home', 'pages.chapter'], function ($view) {
            $topStories = $this->getTopStories();
            $view->with('dailyTopPurchased', $topStories['daily']);
            $view->with('weeklyTopPurchased', $topStories['weekly']);
            $view->with('monthlyTopPurchased', $topStories['monthly']);
        });

        // Composer cho banners - chỉ load khi cần
        View::composer([
            'pages.home',
            'layouts.partials.header',
        ], function ($view) {
            $view->with('banners', $this->getBanners());
        });

    }

    /**
     * Lấy categories - tránh duplicate trong cùng request
     */
    private function getCategories()
    {
        if (self::$categories === null) {
            self::$categories = Category::withCount(['stories' => function ($query) {
                $query->where('status', 'published');
            }])->orderBy('name')->get();
        }
        return self::$categories;
    }

    /**
     * Lấy top stories - tối ưu để tránh duplicate queries
     */
    private function getTopStories()
    {
        $today = Carbon::today();
        $weekAgo = $today->copy()->subDays(7);
        $monthAgo = $today->copy()->subDays(30);

        // Tối ưu: chỉ gọi 1 lần cho mỗi loại, không duplicate
        return [
            'daily' => $this->getTopStoriesPurchased($today),
            'weekly' => $this->getTopStoriesPurchased($weekAgo),
            'monthly' => $this->getTopStoriesPurchased($monthAgo),
        ];
    }

    /**
     * Lấy banners - tránh duplicate trong cùng request
     */
    private function getBanners()
    {
        if (self::$banners === null) {
            self::$banners = Banner::active()
                ->with(['story' => function ($query) {
                    $query->select('id', 'slug', 'is_18_plus', 'title');
                }])
                ->select('id', 'image', 'link', 'story_id')
                ->get();
        }
        return self::$banners;
    }

    /**
     * Tối ưu hóa query để tránh N+1
     */
    private function getTopStoriesPurchased(Carbon $fromDate)
    {
        // Sử dụng single query với subquery để tối ưu
        $storyIds = DB::select("
            SELECT story_id, SUM(purchase_count) as total_purchases, MAX(latest) as latest_purchase_at
            FROM (
                SELECT story_id, COUNT(*) as purchase_count, MAX(created_at) as latest
                FROM story_purchases 
                WHERE created_at >= ?
                GROUP BY story_id
                
                UNION ALL
                
                SELECT chapters.story_id, COUNT(*) as purchase_count, MAX(chapter_purchases.created_at) as latest
                FROM chapter_purchases 
                INNER JOIN chapters ON chapter_purchases.chapter_id = chapters.id
                WHERE chapter_purchases.created_at >= ?
                GROUP BY chapters.story_id
            ) as combined_purchases
            GROUP BY story_id
            ORDER BY total_purchases DESC
            LIMIT 10
        ", [$fromDate, $fromDate]);
        
        $storyIds = collect($storyIds)->pluck('story_id');

        if ($storyIds->isEmpty()) {
            return collect();
        }

        // Lấy stories với một query duy nhất và eager load relationships
        $stories = Story::whereIn('id', $storyIds)
            ->where('status', 'published')
            ->with([
                'categories:id,name,slug',
                'latestChapter' => function ($query) {
                    $query->select('id', 'story_id', 'number', 'created_at')
                        ->where('status', 'published');
                }
            ])
            ->get()
            ->keyBy('id');

        // Lấy purchase data với một query duy nhất
        $purchaseData = DB::select("
            SELECT story_id, SUM(purchase_count) as total_purchases, MAX(latest) as latest_purchase_at
            FROM (
                SELECT story_id, COUNT(*) as purchase_count, MAX(created_at) as latest
                FROM story_purchases 
                WHERE created_at >= ? AND story_id IN (" . $storyIds->implode(',') . ")
                GROUP BY story_id
                
                UNION ALL
                
                SELECT chapters.story_id, COUNT(*) as purchase_count, MAX(chapter_purchases.created_at) as latest
                FROM chapter_purchases 
                INNER JOIN chapters ON chapter_purchases.chapter_id = chapters.id
                WHERE chapter_purchases.created_at >= ? AND chapters.story_id IN (" . $storyIds->implode(',') . ")
                GROUP BY chapters.story_id
            ) as combined_purchases
            GROUP BY story_id
        ", [$fromDate, $fromDate]);
        
        $purchaseData = collect($purchaseData)->keyBy('story_id');

        // Gộp dữ liệu
        return $storyIds->map(function ($storyId) use ($stories, $purchaseData) {
            $story = $stories[$storyId] ?? null;
            $purchase = $purchaseData[$storyId] ?? null;
            
            if (!$story || !$purchase) return null;

            $story->total_purchases = $purchase->total_purchases;
            $story->latest_purchase_at = $purchase->latest_purchase_at;
            $story->latest_purchase_diff = $purchase->latest_purchase_at
                ? Carbon::parse($purchase->latest_purchase_at)->diffForHumans()
                : 'Chưa có ai mua';

            return $story;
        })->filter();
    }
}
