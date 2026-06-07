<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next): mixed
    {
        $locale = session('locale');

        if (! is_string($locale)) {
            $locale = config('app.locale');
        } elseif (! in_array($locale, (array) config('app.locales', []), true)) {
            $locale = config('app.fallback_locale');
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
