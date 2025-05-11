<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role;
        
        // Kiểm tra nếu role của user có trong danh sách roles được cho phép
        if (empty($roles) || in_array($userRole, $roles)) {
            return $next($request);
        }
        
        // Nếu là admin, mặc định cho phép truy cập tất cả
        if ($userRole === 'admin' && !in_array('only_specified', $roles)) {
            return $next($request);
        }
        
        // Nếu là role author, chuyển hướng về trang author
        if ($userRole === 'author') {
            return redirect()->route('user.author.index');
        }
        
        // Nếu là role mod hoặc admin, chuyển hướng về trang admin
        if (in_array($userRole, ['admin', 'mod']) && $userRole !== 'user') {
            return redirect()->route('admin.dashboard');
        }
        
        return abort(403, 'Không có quyền truy cập');
    }
}