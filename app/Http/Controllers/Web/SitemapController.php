<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Seo\PublicSitemapService;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class SitemapController extends Controller
{
    public function __construct(protected PublicSitemapService $sitemap)
    {
    }

    public function index(): Response
    {
        $xml = Cache::remember($this->sitemap->cacheKey(), 3600, fn () => $this->sitemap->renderXml());

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $sitemapUrl = rtrim((string) config('app.url'), '/').'/sitemap.xml';

        // Public auth forms stay Allow so crawlers can read noindex meta.
        // Authenticated / private areas are Disallow'd (still reachable by users).
        $body = implode("\n", [
            'User-agent: *',
            'Allow: /',
            '',
            '# Private areas (users can still open these URLs)',
            'Disallow: /admin',
            'Disallow: /account',
            'Disallow: /advertiser',
            'Disallow: /api/',
            'Disallow: /dashboard',
            'Disallow: /profile',
            '',
            '# Auth forms: crawlable + noindex (not listed in sitemap.xml)',
            'Allow: /login',
            'Allow: /register',
            'Allow: /forgot-password',
            'Allow: /reset-password',
            'Allow: /verify-email',
            'Allow: /confirm-password',
            '',
            'Sitemap: '.$sitemapUrl,
            '',
        ]);

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
