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
                $entity = Story::where('slug', $slug)->first();
            }
        } elseif ($type === 'chapter') {
            // For chapter route: story/{storySlug}/{chapterSlug}
            $chapterSlug = $request->route('chapterSlug');
            if ($chapterSlug) {
                $entity = Chapter::where('slug', $chapterSlug)->first();
            }
        } elseif ($type === 'banner') {
            // For banner route: banner/{banner}
            $bannerId = $request->route('banner');
           
            if ($bannerId) {
                $entity = Banner::where('id', $bannerId->id)->first();
            }
        }
       
        // If we have an entity with affiliate link
        if ($entity && !empty($entity->link_aff)) {
            $entityId = $entity->id;
            $entityType = $type;
            $sessionKey = "aff_{$entityType}_{$entityId}_last_visit";
            
            // Check if we have a session entry and when it was last accessed
            if (Session::has($sessionKey)) {
                $lastVisit = Carbon::parse(Session::get($sessionKey));
                $now = Carbon::now();
                
                // If more than 5 minutes have passed since last visit
                if ($now->diffInMinutes($lastVisit) >= env('AFFILIATE_REDIRECT_INTERVAL', 5)) {
                    // Update the timestamp
                    Session::put($sessionKey, $now);
                    
                    // Redirect to affiliate link
                    return redirect($entity->link_aff);
                }
            } else {
                // First visit, store timestamp and redirect
                Session::put($sessionKey, Carbon::now());
                return redirect($entity->link_aff);
            }
        }
        
        return $next($request);
    }
}