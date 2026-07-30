<?php

namespace App\Support;

use App\Services\Seo\PublicSitemapService;
use Illuminate\Support\Facades\Cache;

/**
 * Invalidates the cached public XML sitemap after content changes.
 */
class SitemapCache
{
    public static function forget(): void
    {
        Cache::forget(app(PublicSitemapService::class)->cacheKey());
    }
}
