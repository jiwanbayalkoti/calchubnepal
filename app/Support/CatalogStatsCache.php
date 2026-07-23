<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class CatalogStatsCache
{
    public const ABOUT_KEY = 'calc_hub:about:stats';

    public const HOME_KEY = 'calc_hub:home:catalog_counts';

    public static function forget(): void
    {
        Cache::forget(self::ABOUT_KEY);
        Cache::forget(self::HOME_KEY);
    }
}
