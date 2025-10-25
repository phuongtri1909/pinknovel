<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Chapter;
use App\Models\Story;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Exception;

class PageController extends Controller
{

    public function contact()
    {
        return view('pages.pages.contact');
    }

    public function privacyPolicy()
    {
        return view('pages.pages.privacy-policy');
    }

    public function terms()
    {
        return view('pages.pages.terms');
    }

    public function contentRules()
    {
        return view('pages.pages.content-rules');
    }

    public function confidental()
    {
        return view('pages.pages.confidental');
    }
}
