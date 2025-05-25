<?php

namespace App\Http\Controllers;

use App\Models\Social;
use Illuminate\Support\Facades\Cache;

class SocialController extends Controller
{
    /**
     * Get all active social media links.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getSocials()
    {
        return Cache::remember('socials', 3600, function () {
            return Social::active()->orderBy('sort_order')->get();
        });
    }
} 