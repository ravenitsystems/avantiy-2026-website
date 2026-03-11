<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locales = config('locales.supported', ['en']);
        $default = config('locales.default', 'en');

        $locale = $request->route('locale');
        if ($locale && in_array($locale, $locales, true)) {
            App::setLocale($locale);
        } else {
            App::setLocale($default);
        }

        return $next($request);
    }
}
