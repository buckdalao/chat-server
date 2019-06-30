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
        if (!app('App\Repositories\Tool\ClientAuthenticateRepository')->authenticate($request->header('custom-token'))) {
            return response()->json([
                'status_code' => 401,
                'data'        => 'Client unauthorized',
                'time'        => time(),
            ], 401);
        }
        return $next($request);
    }
}