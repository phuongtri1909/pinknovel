<?php

namespace App\Observers;

use App\Models\Guide;
use Illuminate\Support\Facades\Cache;

class GuideObserver
{
    /**
     * Clear all guide related cache
     */
    private function clearGuideCache(?Guide $guide = null): void
    {
        // Clear specific guide cache if slug exists
        if ($guide && $guide->slug) {
            Cache::forget("guide_{$guide->slug}");
        }
        
        // Clear latest guide cache
        Cache::forget('guide_latest');
        
        // Clear old cache key (backward compatibility)
        Cache::forget('guide');
    }

    /**
     * Handle the Guide "created" event.
     */
    public function created(Guide $guide): void
    {
        $this->clearGuideCache($guide);
    }

    /**
     * Handle the Guide "updated" event.
     */
    public function updated(Guide $guide): void
    {
        // Get old slug before clearing cache (if slug was changed)
        $oldSlug = $guide->getOriginal('slug');
        
        $this->clearGuideCache($guide);
        
        // Clear cache with old slug if it was changed
        if ($oldSlug && $oldSlug !== $guide->slug) {
            Cache::forget("guide_{$oldSlug}");
        }
    }

    /**
     * Handle the Guide "deleted" event.
     */
    public function deleted(Guide $guide): void
    {
        $this->clearGuideCache($guide);
    }

    /**
     * Handle the Guide "restored" event.
     */
    public function restored(Guide $guide): void
    {
        $this->clearGuideCache($guide);
    }

    /**
     * Handle the Guide "force deleted" event.
     */
    public function forceDeleted(Guide $guide): void
    {
        $this->clearGuideCache($guide);
    }
}
