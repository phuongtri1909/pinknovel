<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GuideController extends Controller
{
    /**
     * Display the list of guides.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $guides = Guide::published()->latest()->get();
        
        return view('pages.guide.index', compact('guides'));
    }

    /**
     * Display the guide detail page.
     *
     * @param  string  $slug
     * @return \Illuminate\View\View
     */
    public function show($slug)
    {
        $guide = Cache::remember("guide_{$slug}", 3600, function () use ($slug) {
            return Guide::published()->where('slug', $slug)->firstOrFail();
        });

        // Get related guides
        $relatedGuides = Guide::published()
            ->where('id', '!=', $guide->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('pages.guide.show', compact('guide', 'relatedGuides'));
    }
    
    /**
     * Get the guide content (for backward compatibility).
     *
     * @return mixed
     */
    public static function getGuide()
    {
        return Cache::remember('guide_latest', 3600, function () {
            return Guide::published()->latest()->first();
        });
    }
} 