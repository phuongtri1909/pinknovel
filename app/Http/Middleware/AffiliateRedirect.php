<?php

namespace App\Http\Middleware;

use App\Models\Story;
use App\Models\Banner;
use App\Models\Chapter;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class AffiliateRedirect
{
    /**
     * Static cache để tránh duplicate queries trong cùng request
     */
    private static $entityCache = [];

    /**
     * Get cached entity by key
     */
    public static function getCachedEntity($key)
    {
        return self::$entityCache[$key] ?? null;
    }

    /**
     * Handle affiliate link redirection.
     */
    public function handle(Request $request, Closure $next, string $type = null): Response
    {
        // Get the entity from the route
        $entity = null;
        
        // Extract the model based on the route parameter name and type
        if ($type === 'story') {
            // For story route: story/{slug}
            $slug = $request->route('slug');
            if ($slug) {
                $cacheKey = "story_{$slug}";
                if (!isset(self::$entityCache[$cacheKey])) {
                    self::$entityCache[$cacheKey] = Story::where('slug', $slug)
                        ->with(['categories' => function ($query) {
                            $query->select('categories.id', 'categories.name', 'categories.slug');
                        }])
                        ->first();
                }
                $entity = self::$entityCache[$cacheKey];
            }
        } elseif ($type === 'chapter') {
            $storySlug = $request->route('storySlug');
            $chapterSlug = $request->route('chapterSlug');
            
            if ($storySlug && $chapterSlug) {
                $cacheKey = "chapter_{$storySlug}_{$chapterSlug}";
                if (!isset(self::$entityCache[$cacheKey])) {
                    $storyCacheKey = "story_{$storySlug}";
                    if (!isset(self::$entityCache[$storyCacheKey])) {
                        self::$entityCache[$storyCacheKey] = Story::where('slug', $storySlug)->first();
                    }
                    $story = self::$entityCache[$storyCacheKey];
                    
                    if ($story) {
                        self::$entityCache[$cacheKey] = Chapter::where('slug', $chapterSlug)
                            ->where('story_id', $story->id)
                            ->first();
                    }
                }
                $entity = self::$entityCache[$cacheKey];
            }
        } elseif ($type === 'banner') {
            $bannerParam = $request->route('banner');
           
            if ($bannerParam) {
                if (is_object($bannerParam) && $bannerParam instanceof Banner) {
                    $bannerId = $bannerParam->id;
                    $cacheKey = "banner_{$bannerId}";
                    if (!isset(self::$entityCache[$cacheKey])) {
                        self::$entityCache[$cacheKey] = $bannerParam;
                    }
                    $entity = self::$entityCache[$cacheKey];
                } else {
                    $bannerId = is_numeric($bannerParam) ? (int)$bannerParam : $bannerParam;
                    $cacheKey = "banner_{$bannerId}";
                    
                    if (!isset(self::$entityCache[$cacheKey])) {
                        self::$entityCache[$cacheKey] = Banner::where('id', $bannerId)->first();
                    }
                    $entity = self::$entityCache[$cacheKey];
                }
            }
        }
       
        if ($entity && !empty($entity->link_aff)) {
            $entityId = $entity->id;
            $entityType = $type;
            $sessionKey = "aff_{$entityType}_{$entityId}_last_visit";
            
            if (Session::has($sessionKey)) {
                $lastVisit = Carbon::parse(Session::get($sessionKey));
                $now = Carbon::now();
                if ($now->diffInMinutes($lastVisit) >= env('AFFILIATE_REDIRECT_INTERVAL', 5)) {
                    Session::put($sessionKey, $now);
                    return redirect($entity->link_aff);
                }
            } else {
                Session::put($sessionKey, Carbon::now());
                return redirect($entity->link_aff);
            }
        }
        
        return $next($request);
    }
}