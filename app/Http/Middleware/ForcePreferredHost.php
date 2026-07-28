<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 301-redirect every public request to APP_URL's scheme + host.
 * Fixes GSC "Duplicate, Google chose different canonical" from
 * http/https and www/non-www variants.
 */
class ForcePreferredHost
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->isProduction()) {
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
        $target = $parts['scheme'].'://'.$parts['host'].$path;

        if ($query = $request->getQueryString()) {
            $target .= '?'.$query;
        }

        return redirect()->to($target, 301);
    }
}
