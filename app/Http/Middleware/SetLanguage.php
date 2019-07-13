<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class SetLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->verifyLocale($request->header('accept-language'))) {
            App::setLocale($request->header('accept-language'));
        }
        return $next($request);
    }

    public function verifyLocale($locale)
    {
        $configLocales = app('config')->get('app.locales');
        if ($locale && in_array($locale, $configLocales) && !App::isLocale($locale)) {
            return true;
        } else {
            return false;
        }
    }
}
