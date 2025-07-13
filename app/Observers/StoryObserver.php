<?php

namespace App\Observers;

use App\Models\Story;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StoryObserver
{
    /**
     * Handle the Story "created" event.
     */
    public function created(Story $story): void
    {
        $this->clearRelevantCaches($story, 'created');
    }

    /**
     * Handle the Story "updated" event.
     */
    public function updated(Story $story): void
    {
        $this->clearRelevantCaches($story, 'updated');
    }

    /**
     * Handle the Story "deleted" event.
     */
    public function deleted(Story $story): void
    {
        $this->clearRelevantCaches($story, 'deleted');
    }

    /**
     * Handle the Story "restored" event.
     */
    public function restored(Story $story): void
    {
        $this->clearRelevantCaches($story, 'restored');
    }

    /**
     * Handle the Story "force deleted" event.
     */
    public function forceDeleted(Story $story): void
    {
        $this->clearRelevantCaches($story, 'forceDeleted');
    }

    /**
     * Clear relevant caches based on story changes
     */
    private function clearRelevantCaches(Story $story, string $action): void
    {
        try {
            $cachesToClear = [];

            $cachesToClear[] = 'hot';

            if ($story->status === 'published' || $story->wasChanged('status')) {
                $cachesToClear[] = 'new';
                $cachesToClear[] = 'latest';
            }

            if ($story->wasChanged('completed') || $story->completed) {
                $cachesToClear[] = 'completed';
            }

            if ($story->wasChanged('is_featured') || $story->wasChanged('featured_order')) {
                $cachesToClear[] = 'hot';
            }

            if ($story->wasChanged('reviewed_at')) {
                $cachesToClear[] = 'new';
            }

            if (in_array($action, ['deleted', 'forceDeleted'])) {
                HomeController::clearAllCaches();
                return;
            }

            if (!empty($cachesToClear)) {
                HomeController::clearCachesByType(array_unique($cachesToClear));
            }

            Log::info("Story cache cleared for story ID: {$story->id}, action: {$action}, caches: " . implode(', ', $cachesToClear));
        } catch (\Exception $e) {
            Log::error("Error clearing story cache: " . $e->getMessage());
            HomeController::clearAllCaches();
        }
    }
}
