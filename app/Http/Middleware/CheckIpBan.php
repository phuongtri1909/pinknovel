<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckIpBan
{
    public function handle($request, Closure $next)
    {
        $ip = $request->ip();

        if(DB::table('banned_ips')->where('ip_address', $ip)->exists()) {
            return abort(403, 'Your IP address is banned');
        } 
        return $next($request);
    }
}
