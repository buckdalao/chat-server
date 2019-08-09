<?php

namespace App\Http\Middleware;

use Closure;

class ClientAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!app('App\Libs\ApiAuthenticate')->setRequest($request)->verify()) {
            return response()->json([
                'status_code' => 401,
                'data'        => __('the client is not authorized'),
                'time'        => time(),
            ], 401);
        }
        return $next($request);
    }
}
