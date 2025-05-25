<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\GuideController;

class GuideServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('layouts.partials.header', function ($view) {
            try {
                $view->with('guide', GuideController::getGuide());
            } catch (\Exception $e) {
                $view->with('guide', null);
            }
        });
    }
} 