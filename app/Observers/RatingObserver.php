<?php

namespace App\Observers;

use App\Models\Rating;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RatingObserver
{
    /**
     * Handle the Rating "created" event.
     */
    public function created(Rating $rating): void
    {
        $this->clearRelevantCaches($rating, 'created');
    }

    /**
     * Handle the Rating "updated" event.
     */
    public function updated(Rating $rating): void
    {
        $this->clearRelevantCaches($rating, 'updated');
    }

    /**
     * Handle the Rating "deleted" event.
     */
    public function deleted(Rating $rating): void
    {
        $this->clearRelevantCaches($rating, 'deleted');
    }

    /**
     * Handle the Rating "restored" event.
     */
    public function restored(Rating $rating): void
    {
        $this->clearRelevantCaches($rating, 'restored');
    }

    /**
     * Handle the Rating "force deleted" event.
     */
    public function forceDeleted(Rating $rating): void
    {
        $this->clearRelevantCaches($rating, 'forceDeleted');
    }

    /**
     * Clear relevant caches based on rating changes
     */
    private function clearRelevantCaches(Rating $rating, string $action): void
    {
        try {
            // Rating changes affect rating stories, hot stories, and latest updated
            $cachesToClear = ['rating', 'hot', 'latest'];

            HomeController::clearCachesByType($cachesToClear);

            Log::info("Rating cache cleared for rating ID: {$rating->id}, action: {$action}, caches: " . implode(', ', $cachesToClear));
        } catch (\Exception $e) {
            Log::error("Error clearing rating cache: " . $e->getMessage());
            // Fallback: clear related caches
            HomeController::clearCachesByType(['rating', 'hot', 'latest']);
        }
    }
}
