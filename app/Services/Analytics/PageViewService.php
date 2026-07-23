<?php

namespace App\Services\Analytics;

use App\Models\BlogPost;
use App\Models\Calculator;
use App\Models\PageView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Persists public page views for the admin Analytics dashboard.
 *
 * Country prefers CDN/proxy geo headers, then a cached IP lookup.
 * IP is stored truncated for admin abuse review only — never sent to AdSense
 * or used for client-side advertising personalization.
 */
class PageViewService
{
    /** Seconds before the same session can re-count the same path. */
    private const DEDUPE_SECONDS = 1800;

    public function __construct(protected GeoCountryResolver $geo)
    {
    }

    public function record(Request $request, ?Model $calculable = null): void
    {
        try {
            $path = '/'.ltrim($request->path(), '/');
            if ($path === '//') {
                $path = '/';
            }

            $sessionId = '';
            if ($request->hasSession()) {
                $sessionId = (string) ($request->session()->getId() ?: '');
            }

            $ip = $request->ip();
            $dedupeKey = 'calc_hub:pv:'.sha1(($sessionId !== '' ? $sessionId : ($ip ?? 'anon')).'|'.$path);

            if (! Cache::add($dedupeKey, 1, self::DEDUPE_SECONDS)) {
                return;
            }

            $calculable ??= $this->resolveCalculable($request);

            PageView::query()->create([
                'calculable_type' => $calculable?->getMorphClass(),
                'calculable_id' => $calculable?->getKey(),
                'path' => mb_substr($path, 0, 255),
                'referrer' => $this->referrer($request),
                'country' => $this->resolveCountry($request, $ip),
                'device' => $this->device($request->userAgent()),
                'ip_hash' => $this->ipHash($ip),
                'ip_truncated' => $this->truncateIp($ip),
                'session_id' => $sessionId !== '' ? mb_substr($sessionId, 0, 191) : null,
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::warning('Page view recording failed.', [
                'message' => $e->getMessage(),
                'path' => $request->path(),
            ]);
        }
    }

    protected function resolveCountry(Request $request, ?string $ip): ?string
    {
        return $this->geo->fromHeaders($request->headers->all())
            ?? $this->geo->fromIp($ip);
    }

    protected function resolveCalculable(Request $request): ?Model
    {
        if ($request->routeIs('calculators.show')) {
            $calculator = $request->route('calculator');

            if ($calculator instanceof Calculator) {
                return $calculator;
            }

            if (is_string($calculator) && $calculator !== '') {
                return Calculator::query()->where('slug', $calculator)->first();
            }
        }

        if ($request->routeIs('blog.show')) {
            $post = $request->route('post');

            if ($post instanceof BlogPost) {
                return $post;
            }

            if (is_string($post) && $post !== '') {
                return BlogPost::query()->where('slug', $post)->first();
            }
        }

        return null;
    }

    protected function referrer(Request $request): ?string
    {
        $referrer = $request->headers->get('referer');
        if (! filled($referrer)) {
            return null;
        }

        return mb_substr($referrer, 0, 255);
    }

    protected function device(?string $userAgent): string
    {
        $ua = strtolower((string) $userAgent);

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

    protected function ipHash(?string $ip): ?string
    {
        if (! filled($ip)) {
            return null;
        }

        return hash('sha256', $ip.'|'.(string) config('app.key'));
    }

    /**
     * Privacy-preserving IP for admin abuse review (IPv4 /24, IPv6 /48-ish).
     * Not used for ads, personalization, or public pages.
     */
    protected function truncateIp(?string $ip): ?string
    {
        if (! filled($ip)) {
            return null;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            if (count($parts) === 4) {
                $parts[3] = '0';

                return implode('.', $parts);
            }
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $expanded = inet_pton($ip);
            if ($expanded === false) {
                return null;
            }
            // Zero the last 80 bits ≈ /48 network prefix for reporting.
            $expanded = substr($expanded, 0, 6).str_repeat("\0", 10);

            return inet_ntop($expanded) ?: null;
        }

        return null;
    }
}
