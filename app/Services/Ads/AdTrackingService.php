<?php

namespace App\Services\Ads;

use App\Models\Advertisement;
use App\Models\AdvertisementClick;
use App\Models\AdvertisementImpression;
use App\Models\AdsenseImpression;
use App\Services\Analytics\GeoCountryResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Throwable;

class AdTrackingService
{
    private const IMPRESSION_DEDUPE_SECONDS = 60;

    private const CLICK_DEDUPE_SECONDS = 30;

    public function __construct(protected GeoCountryResolver $geo)
    {
    }

    public function trackImpression(Advertisement $ad, Request $request): void
    {
        $ip = (string) ($request->ip() ?? '');
        $dedupe = 'calc_hub:ad:imp:'.$ad->id.':'.sha1($ip.'|'.($request->session()->getId() ?? ''));

        if (! Cache::add($dedupe, 1, self::IMPRESSION_DEDUPE_SECONDS)) {
            return;
        }

        $meta = $this->requestMeta($request);

        if ($ad->advertiser_id) {
            AdvertisementImpression::query()->create([
                'advertisement_id' => $ad->id,
                'advertiser_id' => $ad->advertiser_id,
                'ip_address' => $meta['ip_address'],
                'device' => $meta['device'],
                'browser' => $meta['browser'],
                'country' => $meta['country'],
                'city' => $meta['city'],
                'viewed_at' => now(),
            ]);
        }

        if ($ad->ad_type === 'adsense' || filled($ad->adsense_code)) {
            AdsenseImpression::query()->create([
                'position' => $ad->position,
                'ad_slot' => null,
                'source' => 'advertisement',
                'advertisement_id' => $ad->id,
                'ip_address' => $meta['ip_address'],
                'device' => $meta['device'],
                'browser' => $meta['browser'],
                'country' => $meta['country'],
                'viewed_at' => now(),
            ]);
        }

        $ad->recordImpression();
    }

    public function trackAdsenseUnit(Request $request, string $position, ?string $slot = null): void
    {
        $position = preg_replace('/[^a-z0-9_\\-]/i', '', $position) ?: 'sidebar';
        $ip = (string) ($request->ip() ?? '');
        $dedupe = 'calc_hub:adsense:imp:'.$position.':'.sha1($ip.'|'.($request->session()->getId() ?? ''));

        if (! Cache::add($dedupe, 1, self::IMPRESSION_DEDUPE_SECONDS)) {
            return;
        }

        $meta = $this->requestMeta($request);

        AdsenseImpression::query()->create([
            'position' => mb_substr($position, 0, 64),
            'ad_slot' => $slot ? mb_substr($slot, 0, 64) : null,
            'source' => 'unit',
            'advertisement_id' => null,
            'ip_address' => $meta['ip_address'],
            'device' => $meta['device'],
            'browser' => $meta['browser'],
            'country' => $meta['country'],
            'viewed_at' => now(),
        ]);
    }

    public function trackClick(Advertisement $ad, Request $request): ?string
    {
        $target = $ad->link_url ?: url('/');

        if ($ad->advertiser_id) {
            $ip = (string) ($request->ip() ?? '');
            $dedupe = 'calc_hub:ad:clk:'.$ad->id.':'.sha1($ip.'|'.($request->session()->getId() ?? ''));

            if (Cache::add($dedupe, 1, self::CLICK_DEDUPE_SECONDS)) {
                $meta = $this->requestMeta($request);

                AdvertisementClick::query()->create([
                    'advertisement_id' => $ad->id,
                    'advertiser_id' => $ad->advertiser_id,
                    'ip_address' => $meta['ip_address'],
                    'device' => $meta['device'],
                    'browser' => $meta['browser'],
                    'country' => $meta['country'],
                    'city' => $meta['city'],
                    'clicked_at' => now(),
                ]);

                $ad->recordClick();
            }
        } else {
            $ad->recordClick();
        }

        return $target;
    }

    /**
     * @return array{ip_address: ?string, device: string, browser: string, country: ?string, city: ?string}
     */
    protected function requestMeta(Request $request): array
    {
        $ip = $request->ip();
        $ua = (string) $request->userAgent();

        $country = null;
        try {
            $country = $this->geo->fromHeaders($request->headers->all())
                ?? $this->geo->fromIp($ip);
        } catch (Throwable) {
            $country = null;
        }

        return [
            'ip_address' => $ip ? mb_substr($ip, 0, 45) : null,
            'device' => $this->device($ua),
            'browser' => $this->browser($ua),
            'country' => $country,
            'city' => null,
        ];
    }

    protected function device(string $ua): string
    {
        $ua = strtolower($ua);

        if ($ua === '') {
            return 'unknown';
        }

        if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) {
            return 'tablet';
        }

        if (str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone')) {
            return 'mobile';
        }

        return 'desktop';
    }

    protected function browser(string $ua): string
    {
        $uaLower = strtolower($ua);

        return match (true) {
            str_contains($uaLower, 'edg/') => 'Edge',
            str_contains($uaLower, 'chrome/') && ! str_contains($uaLower, 'edg/') => 'Chrome',
            str_contains($uaLower, 'firefox/') => 'Firefox',
            str_contains($uaLower, 'safari/') && ! str_contains($uaLower, 'chrome/') => 'Safari',
            str_contains($uaLower, 'opr/') || str_contains($uaLower, 'opera') => 'Opera',
            default => 'Other',
        };
    }
}
