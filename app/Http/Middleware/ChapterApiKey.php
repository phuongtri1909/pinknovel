<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChapterApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('secret-chapter');
       
        if (!$apiKey || $apiKey !== env('CHAPTER_API_KEY')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid API key'
            ], 401);
        }

        return $next($request);
    }
}
