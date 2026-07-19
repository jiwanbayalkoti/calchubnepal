<?php

namespace App\Http\Middleware;

use App\Services\Settings\AppSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the active locale from session, then authenticated user preference,
 * then Admin Settings / config default.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $hub = app(AppSettings::class);
        $supported = (array) config('calculator_hub.locales', ['en']);
        $fallback = $hub->defaultLocale();

        $locale = session('locale');

        if (! is_string($locale) || ! in_array($locale, $supported, true)) {
            $userLocale = $request->user()?->locale;
            $locale = (is_string($userLocale) && in_array($userLocale, $supported, true))
                ? $userLocale
                : $fallback;

            session(['locale' => $locale]);
        }

        if (! in_array($locale, $supported, true)) {
            $locale = $fallback;
            session(['locale' => $locale]);
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
