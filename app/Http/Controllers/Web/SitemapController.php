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
        $body = implode("\n", [
            'User-agent: *',
            'Allow: /',
            '',
            'Disallow: /admin',
            'Disallow: /account',
            'Disallow: /advertiser',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /dashboard',
            'Disallow: /profile',
            'Disallow: /api/',
            '',
            'Sitemap: '.$sitemapUrl,
            '',
        ]);

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
