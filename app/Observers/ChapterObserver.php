<?php

namespace App\Observers;

use App\Models\Chapter;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ChapterObserver
{
    /**
     * Handle the Chapter "created" event.
     */
    public function created(Chapter $chapter): void
    {
        $this->clearRelevantCaches($chapter, 'created');
    }

    /**
     * Handle the Chapter "updated" event.
     */
    public function updated(Chapter $chapter): void
    {
        $this->clearRelevantCaches($chapter, 'updated');
    }

    /**
     * Handle the Chapter "deleted" event.
     */
    public function deleted(Chapter $chapter): void
    {
        $this->clearRelevantCaches($chapter, 'deleted');
    }

    /**
     * Handle the Chapter "restored" event.
     */
    public function restored(Chapter $chapter): void
    {
        $this->clearRelevantCaches($chapter, 'restored');
    }

    /**
     * Handle the Chapter "force deleted" event.
     */
    public function forceDeleted(Chapter $chapter): void
    {
        $this->clearRelevantCaches($chapter, 'forceDeleted');
    }

    /**
     * Clear relevant caches based on chapter changes
     */
    private function clearRelevantCaches(Chapter $chapter, string $action): void
    {
        try {
            $cachesToClear = [];

            // Check if chapter status changed to published
            if ($chapter->status === 'published' || $chapter->wasChanged('status')) {
                $cachesToClear[] = 'latest';
                $cachesToClear[] = 'hot';
            }

            // Check if views changed (affects top viewed)
            if ($chapter->wasChanged('views')) {
                $cachesToClear[] = 'viewed';
            }

            // Check if chapter was published (affects latest updated)
            if ($chapter->wasChanged('created_at') || $chapter->wasChanged('scheduled_publish_at')) {
                $cachesToClear[] = 'latest';
            }

            // For deleted chapters, clear relevant caches
            if (in_array($action, ['deleted', 'forceDeleted'])) {
                $cachesToClear = ['latest', 'hot', 'viewed'];
            }

            // Clear specific caches
            if (!empty($cachesToClear)) {
                HomeController::clearCachesByType(array_unique($cachesToClear));
            }

            Log::info("Chapter cache cleared for chapter ID: {$chapter->id}, action: {$action}, caches: " . implode(', ', $cachesToClear));
        } catch (\Exception $e) {
            Log::error("Error clearing chapter cache: " . $e->getMessage());
            // Fallback: clear related caches
            HomeController::clearCachesByType(['latest', 'hot', 'viewed']);
        }
    }
}
