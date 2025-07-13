<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Constants\CacheKeys;
use App\Http\Controllers\HomeController;

class TestCacheCommand extends Command
{
    protected $signature = 'cache:test-home';
    protected $description = 'Test home page cache functionality';

    public function handle()
    {
        $this->info('Testing home page cache...');

        // Test cache keys
        $this->info('Cache keys:');
        $this->line('- Hot stories: ' . CacheKeys::HOME_HOT_STORIES);
        $this->line('- New stories: ' . CacheKeys::HOME_NEW_STORIES);
        $this->line('- Duration: ' . CacheKeys::DURATION . ' seconds');

        // Check if cache has data
        $this->info("\nChecking cache status:");
        $cacheKeys = [
            'Hot Stories' => CacheKeys::HOME_HOT_STORIES,
            'New Stories' => CacheKeys::HOME_NEW_STORIES,
            'Rating Stories' => CacheKeys::HOME_RATING_STORIES,
            'Latest Updated' => CacheKeys::HOME_LATEST_UPDATED_STORIES,
            'Top Viewed' => CacheKeys::HOME_TOP_VIEWED_STORIES,
            'Top Followed' => CacheKeys::HOME_TOP_FOLLOWED_STORIES,
            'Completed Stories' => CacheKeys::HOME_COMPLETED_STORIES,
        ];

        foreach ($cacheKeys as $name => $key) {
            $hasCache = Cache::has($key);
            $status = $hasCache ? '✓ Cached' : '✗ Not cached';
            $this->line("- {$name}: {$status}");
        }

        // Clear all caches
        $this->info("\nClearing all caches...");
        HomeController::clearAllCaches();
        $this->info('✓ All caches cleared');

        return 0;
    }
}
