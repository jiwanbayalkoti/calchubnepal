<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirect browsers to APP_URL scheme+host (SEO / GSC duplicates).
 *
 * IMPORTANT: only redirects safe methods (GET/HEAD). Redirecting POST with 301
 * drops the body and causes CSRF token mismatch on login / QR / forms.
 */
class ForcePreferredHost
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->isProduction()) {
            return $next($request);
        }

        // Never redirect form/AJAX submissions — keeps session + CSRF on same host.
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        $preferred = rtrim((string) config('app.url'), '/');
        $parts = parse_url($preferred);

        if (empty($parts['scheme']) || empty($parts['host'])) {
            return $next($request);
        }

        $schemeOk = $request->getScheme() === $parts['scheme'];
        $hostOk = strcasecmp($request->getHost(), $parts['host']) === 0;

        if ($schemeOk && $hostOk) {
            return $next($request);
        }

        $path = $request->getPathInfo() ?: '/';
        $target = rtrim($preferred, '/').$path;

        if ($query = $request->getQueryString()) {
            $target .= '?'.$query;
        }

        return redirect()->to($target, 301);
    }
}
