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
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldTrack($request, $response)) {
            // Defer analytics I/O (DB + optional geo HTTP) until after the response
            // is sent so TTFB / LCP are not blocked by page-view recording.
            // Do not capture $request / PageViewService — they hold PDO and cannot
            // be serialized into CallQueuedClosure (even on the sync queue).
            dispatch(static function (): void {
                app(PageViewService::class)->record(request());
            })->afterResponse();
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

        // Tracking pixels / binary assets must never count as page views.
        if ($this->isNonHtmlResponse($response)) {
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
            'advertiser',
            'advertiser/*',
            'ads',
            'ads/*',
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
            'robots.txt',
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
            'ads.impression',
            'ads.adsense.impression',
            'ads.click',
            'auth.google.redirect',
            'auth.google.callback',
        );
    }

    protected function isNonHtmlResponse(Response $response): bool
    {
        $contentType = strtolower((string) $response->headers->get('Content-Type', ''));

        if ($contentType === '') {
            return false;
        }

        return str_starts_with($contentType, 'image/')
            || str_starts_with($contentType, 'font/')
            || str_starts_with($contentType, 'audio/')
            || str_starts_with($contentType, 'video/')
            || str_contains($contentType, 'application/octet-stream')
            || str_contains($contentType, 'application/javascript')
            || str_contains($contentType, 'text/css');
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
