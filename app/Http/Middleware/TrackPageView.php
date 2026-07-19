<?php

namespace App\Http\Middleware;

use App\Services\Analytics\PageViewService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records successful public GET page views after the response is prepared.
 */
class TrackPageView
{
    public function __construct(protected PageViewService $pageViews)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldTrack($request, $response)) {
            $this->pageViews->record($request);
        }

        return $response;
    }

    protected function shouldTrack(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        if ($request->ajax() || $request->expectsJson()) {
            return false;
        }

        if (! $response->isSuccessful()) {
            return false;
        }

        if ($this->isExcludedPath($request)) {
            return false;
        }

        if ($this->looksLikeBot($request->userAgent())) {
            return false;
        }

        return true;
    }

    protected function isExcludedPath(Request $request): bool
    {
        if ($request->is(
            'admin',
            'admin/*',
            'api',
            'api/*',
            'account',
            'account/*',
            'sanctum/*',
            'up',
            'livewire/*',
            '_debugbar/*',
            'telescope/*',
            'horizon/*',
            'storage/*',
            'build/*',
            'css/*',
            'js/*',
            'favicon*',
            'sitemap.xml',
            'auth/google',
            'auth/google/*',
        )) {
            return true;
        }

        return $request->routeIs(
            'login',
            'register',
            'password.*',
            'verification.*',
            'locale.switch',
            'sitemap.xml',
            'auth.google.redirect',
            'auth.google.callback',
        );
    }

    protected function looksLikeBot(?string $userAgent): bool
    {
        $ua = strtolower((string) $userAgent);

        if ($ua === '') {
            return true;
        }

        return (bool) preg_match(
            '/bot|crawl|spider|slurp|facebookexternalhit|preview|wget|curl|python-requests|headless/i',
            $ua
        );
    }
}
