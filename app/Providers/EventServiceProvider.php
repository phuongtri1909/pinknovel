<?php

namespace App\Providers;

use App\Models\Story;
use App\Models\Chapter;
use App\Models\Rating;
use App\Models\Bookmark;
use App\Observers\StoryObserver;
use App\Observers\ChapterObserver;
use App\Observers\RatingObserver;
use App\Observers\BookmarkObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        Story::observe(StoryObserver::class);
        Chapter::observe(ChapterObserver::class);
        Rating::observe(RatingObserver::class);
        Bookmark::observe(BookmarkObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
