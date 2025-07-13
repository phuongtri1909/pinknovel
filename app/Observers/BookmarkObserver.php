<?php

namespace App\Observers;

use App\Models\Bookmark;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BookmarkObserver
{
    /**
     * Handle the Bookmark "created" event.
     */
    public function created(Bookmark $bookmark): void
    {
        $this->clearRelevantCaches($bookmark, 'created');
    }

    /**
     * Handle the Bookmark "deleted" event.
     */
    public function deleted(Bookmark $bookmark): void
    {
        $this->clearRelevantCaches($bookmark, 'deleted');
    }

    /**
     * Handle the Bookmark "restored" event.
     */
    public function restored(Bookmark $bookmark): void
    {
        $this->clearRelevantCaches($bookmark, 'restored');
    }

    /**
     * Handle the Bookmark "force deleted" event.
     */
    public function forceDeleted(Bookmark $bookmark): void
    {
        $this->clearRelevantCaches($bookmark, 'forceDeleted');
    }

    /**
     * Clear relevant caches based on bookmark changes
     */
    private function clearRelevantCaches(Bookmark $bookmark, string $action): void
    {
        try {
            // Bookmark changes affect top followed stories and hot stories
            $cachesToClear = ['followed', 'hot'];

            HomeController::clearCachesByType($cachesToClear);

            Log::info("Bookmark cache cleared for bookmark ID: {$bookmark->id}, action: {$action}, caches: " . implode(', ', $cachesToClear));
        } catch (\Exception $e) {
            Log::error("Error clearing bookmark cache: " . $e->getMessage());
            // Fallback: clear related caches
            HomeController::clearCachesByType(['followed', 'hot']);
        }
    }
}
