<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckBan
{
    public function handle($request, Closure $next, $banType)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $isAjax = $request->ajax();

        if ($banType === 'ban_login' && $user->ban_login) {
            Auth::logout();
            return $isAjax
                ? response()->json(['status' => 'error', 'message' => 'Tài khoản của bạn đã bị cấm đăng nhập'], 403)
                : redirect()->route('home')->with('error', 'Tài khoản của bạn đã bị cấm đăng nhập');
        }

        if ($banType === 'ban_read' && $user->ban_read) {
            return $isAjax
                ? response()->json(['status' => 'error', 'message' => 'Tài khoản của bạn đã bị cấm đọc truyện'], 403)
                : redirect()->route('home')->with('error', 'Tài khoản của bạn đã bị cấm đọc truyện');
        }

        if ($banType === 'ban_comment' && $user->ban_comment) {
            return $isAjax
                ? response()->json(['status' => 'error', 'message' => 'Tài khoản của bạn đã bị cấm bình luận'], 403)
                : redirect()->route('home')->with('error', 'Tài khoản của bạn đã bị cấm bình luận');
        }

        if ($banType === 'ban_rate' && $user->ban_rate) {
            return $isAjax
                ? response()->json(['status' => 'error', 'message' => 'Tài khoản của bạn đã bị cấm đánh giá'], 403)
                : redirect()->route('home')->with('error', 'Tài khoản của bạn đã bị cấm đánh giá');
        }

        return $next($request);
    }
}