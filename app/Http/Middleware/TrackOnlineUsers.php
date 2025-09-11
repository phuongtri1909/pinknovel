<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\OnlineUser;
use Illuminate\Support\Facades\Auth;

class TrackOnlineUsers
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $sessionId = $request->session()->getId();
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $currentPage = $request->fullUrl();
        $referer = $request->header('referer');
        $userId = Auth::id();

        if (strlen($currentPage) > 65535) {
            $currentPage = substr($currentPage, 0, 65535);
        }
        if ($referer && strlen($referer) > 65535) {
            $referer = substr($referer, 0, 65535);
        }
        if ($userAgent && strlen($userAgent) > 65535) {
            $userAgent = substr($userAgent, 0, 65535);
        }

        OnlineUser::where('last_activity', '<', now()->subMinutes(5))->delete();

        OnlineUser::updateOrCreate(
            [
                'session_id' => $sessionId,
            ],
            [
                'user_id' => $userId,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'current_page' => $currentPage,
                'referer' => $referer,
                'last_activity' => now(),
            ]
        );

        return $next($request);
    }
}
