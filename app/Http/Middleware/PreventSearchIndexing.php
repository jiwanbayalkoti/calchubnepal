<?php

namespace App\Http\Middleware;

use App\Services\Seo\SeoService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds X-Robots-Tag for auth / account / admin URLs so crawlers
 * honour noindex even when HTML meta is missing (redirects, errors).
 */
class PreventSearchIndexing
{
    public function __construct(protected SeoService $seo)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if ($this->seo->shouldNoindexRequest($request)) {
            $response->headers->set('X-Robots-Tag', 'noindex, follow');
        }

        return $response;
    }
}
