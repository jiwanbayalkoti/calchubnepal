<?php

namespace App\Services\Analytics;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Resolves ISO-3166 alpha-2 country codes for analytics.
 * Prefers CDN geo headers; falls back to a cached public-IP lookup.
 */
class GeoCountryResolver
{
    private const CACHE_TTL_SECONDS = 60 * 60 * 24 * 30;

    /**
     * @param  array<string, string|null>  $headers  Lower/upper case header map (request headers)
     */
    public function fromHeaders(iterable $headers): ?string
    {
        $wanted = [
            'cf-ipcountry',
            'cloudfront-viewer-country',
            'x-appengine-country',
            'x-country-code',
            'x-geo-country',
            'x-vercel-ip-country',
        ];

        $map = [];
        foreach ($headers as $key => $value) {
            $map[strtolower((string) $key)] = is_array($value) ? ($value[0] ?? null) : $value;
        }

        foreach ($wanted as $header) {
            $value = strtoupper(trim((string) ($map[$header] ?? '')));
            if ($this->isValidCountryCode($value)) {
                return $value;
            }
        }

        return null;
    }

    public function fromIp(?string $ip): ?string
    {
        if (! filled($ip) || $this->isPrivateIp($ip)) {
            return null;
        }

        $cacheKey = 'calc_hub:geo:country:'.sha1($ip);

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($ip): ?string {
            return $this->lookupIp($ip);
        });
    }

    /**
     * Resolve for a truncated IPv4/IPv6 used in stored analytics rows.
     */
    public function fromTruncatedIp(?string $truncated): ?string
    {
        if (! filled($truncated)) {
            return null;
        }

        if (filter_var($truncated, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $truncated);
            if (count($parts) === 4) {
                // Use .1 inside the /24 for a representative public lookup.
                $parts[3] = '1';

                return $this->fromIp(implode('.', $parts));
            }
        }

        if (filter_var($truncated, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $this->fromIp($truncated);
        }

        return null;
    }

    public function isValidCountryCode(string $code): bool
    {
        return $code !== ''
            && preg_match('/^[A-Z]{2}$/', $code) === 1
            && ! in_array($code, ['XX', 'T1', 'A1', 'A2', 'O1'], true);
    }

    protected function isPrivateIp(string $ip): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            return true;
        }

        return ! filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }

    protected function lookupIp(string $ip): ?string
    {
        try {
            // ipwho.is — free HTTPS, no API key, suitable for low-volume analytics.
            $response = Http::timeout(2)
                ->acceptJson()
                ->get('https://ipwho.is/'.urlencode($ip), [
                    'fields' => 'success,country_code',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (($data['success'] ?? false) === true) {
                    $code = strtoupper(trim((string) ($data['country_code'] ?? '')));
                    if ($this->isValidCountryCode($code)) {
                        return $code;
                    }
                }
            }
        } catch (Throwable $e) {
            Log::debug('Geo country lookup failed.', [
                'ip' => $ip,
                'message' => $e->getMessage(),
            ]);
        }

        try {
            // Fallback: ip-api.com (HTTP only on free tier).
            $response = Http::timeout(2)
                ->acceptJson()
                ->get('http://ip-api.com/json/'.urlencode($ip), [
                    'fields' => 'status,countryCode',
                ]);

            if ($response->successful() && ($response->json('status') === 'success')) {
                $code = strtoupper(trim((string) $response->json('countryCode')));
                if ($this->isValidCountryCode($code)) {
                    return $code;
                }
            }
        } catch (Throwable $e) {
            Log::debug('Geo country fallback lookup failed.', [
                'ip' => $ip,
                'message' => $e->getMessage(),
            ]);
        }

        return null;
    }
}
