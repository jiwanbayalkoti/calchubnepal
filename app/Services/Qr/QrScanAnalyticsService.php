<?php

namespace App\Services\Qr;

use App\Models\QrCode;
use App\Models\QrScan;
use App\Services\Analytics\GeoCountryResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class QrScanAnalyticsService
{
    public function __construct(
        protected GeoCountryResolver $geo,
        protected UserAgentParser $uaParser,
    ) {
    }

    public function record(QrCode $qrCode, Request $request): ?QrScan
    {
        try {
            $ua = (string) $request->userAgent();
            $parsed = $this->uaParser->parse($ua);
            $ip = $request->ip();
            $sessionId = $request->hasSession() ? (string) $request->session()->getId() : '';
            $country = $this->geo->fromHeaders($request->headers->all()) ?? $this->geo->fromIp($ip);

            $scan = QrScan::query()->create([
                'qr_code_id' => $qrCode->id,
                'scanned_at' => now(),
                'country' => $country,
                'city' => $this->resolveCity($request),
                'latitude' => null,
                'longitude' => null,
                'device' => $parsed['device'],
                'browser' => $parsed['browser'],
                'os' => $parsed['os'],
                'referrer' => $this->referrer($request),
                'ip_hash' => $this->ipHash($ip),
                'ip_truncated' => $this->truncateIp($ip),
                'session_id' => $sessionId !== '' ? mb_substr($sessionId, 0, 100) : null,
                'user_agent' => mb_substr($ua, 0, 1000),
            ]);

            QrCode::query()->whereKey($qrCode->id)->update([
                'scan_count' => DB::raw('scan_count + 1'),
                'last_scanned_at' => now(),
            ]);

            return $scan;
        } catch (Throwable $e) {
            Log::warning('QR scan recording failed.', [
                'qr_code_id' => $qrCode->id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @return array{
     *     total: int,
     *     today: int,
     *     this_week: int,
     *     this_month: int,
     *     daily: list<array{date: string, scans: int}>,
     *     monthly: list<array{month: string, scans: int}>,
     *     countries: list<array{country: string, scans: int}>,
     *     devices: list<array{device: string, scans: int}>,
     *     browsers: list<array{browser: string, scans: int}>,
     *     operating_systems: list<array{os: string, scans: int}>
     * }
     */
    public function report(QrCode $qrCode, int $dailyDays = 30, int $monthlyMonths = 12): array
    {
        $cacheKey = "calc_hub:qr:analytics:{$qrCode->id}:{$dailyDays}:{$monthlyMonths}";

        return Cache::remember($cacheKey, 120, function () use ($qrCode, $dailyDays, $monthlyMonths) {
            $base = QrScan::query()->where('qr_code_id', $qrCode->id);

            $today = (clone $base)->whereDate('scanned_at', today())->count();
            $thisWeek = (clone $base)->where('scanned_at', '>=', now()->subDays(7))->count();
            $thisMonth = (clone $base)->where('scanned_at', '>=', now()->startOfMonth())->count();
            $total = (int) $qrCode->scan_count;

            $daily = $this->dailySeries($qrCode->id, $dailyDays);
            $monthly = $this->monthlySeries($qrCode->id, $monthlyMonths);

            return [
                'total' => $total,
                'today' => $today,
                'this_week' => $thisWeek,
                'this_month' => $thisMonth,
                'daily' => $daily,
                'monthly' => $monthly,
                'countries' => $this->groupCounts($qrCode->id, 'country', 'country'),
                'devices' => $this->groupCounts($qrCode->id, 'device', 'device'),
                'browsers' => $this->groupCounts($qrCode->id, 'browser', 'browser'),
                'operating_systems' => $this->groupCounts($qrCode->id, 'os', 'os'),
            ];
        });
    }

    public function forgetCache(QrCode $qrCode): void
    {
        Cache::forget("calc_hub:qr:analytics:{$qrCode->id}:30:12");
        Cache::forget("calc_hub:qr:analytics:{$qrCode->id}:14:6");
    }

    /**
     * @return list<array{date: string, scans: int}>
     */
    protected function dailySeries(int $qrCodeId, int $days): array
    {
        $since = now()->subDays($days - 1)->startOfDay();
        $rows = QrScan::query()
            ->select(DB::raw('DATE(scanned_at) as day'), DB::raw('COUNT(*) as scans'))
            ->where('qr_code_id', $qrCodeId)
            ->where('scanned_at', '>=', $since)
            ->groupBy('day')
            ->pluck('scans', 'day');

        $series = [];
        for ($i = 0; $i < $days; $i++) {
            $day = $since->copy()->addDays($i)->toDateString();
            $series[] = [
                'date' => $day,
                'scans' => (int) ($rows[$day] ?? 0),
            ];
        }

        return $series;
    }

    /**
     * @return list<array{month: string, scans: int}>
     */
    protected function monthlySeries(int $qrCodeId, int $months): array
    {
        $since = now()->subMonths($months - 1)->startOfMonth();
        $rows = QrScan::query()
            ->select(DB::raw("DATE_FORMAT(scanned_at, '%Y-%m') as month"), DB::raw('COUNT(*) as scans'))
            ->where('qr_code_id', $qrCodeId)
            ->where('scanned_at', '>=', $since)
            ->groupBy('month')
            ->pluck('scans', 'month');

        $series = [];
        for ($i = 0; $i < $months; $i++) {
            $month = $since->copy()->addMonths($i)->format('Y-m');
            $series[] = [
                'month' => $month,
                'scans' => (int) ($rows[$month] ?? 0),
            ];
        }

        return $series;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function groupCounts(int $qrCodeId, string $column, string $keyName): array
    {
        return QrScan::query()
            ->select($column, DB::raw('COUNT(*) as scans'))
            ->where('qr_code_id', $qrCodeId)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->groupBy($column)
            ->orderByDesc('scans')
            ->limit(20)
            ->get()
            ->map(fn ($row) => [
                $keyName => (string) $row->{$column},
                'scans' => (int) $row->scans,
            ])
            ->values()
            ->all();
    }

    protected function referrer(Request $request): ?string
    {
        $referrer = $request->headers->get('referer');
        if (! filled($referrer)) {
            return null;
        }

        return mb_substr($referrer, 0, 255);
    }

    protected function resolveCity(Request $request): ?string
    {
        foreach (['x-vercel-ip-city', 'cf-ipcity', 'x-city'] as $header) {
            $value = trim((string) $request->headers->get($header));
            if ($value !== '') {
                return mb_substr(urldecode($value), 0, 80);
            }
        }

        return null;
    }

    protected function ipHash(?string $ip): ?string
    {
        if (! filled($ip)) {
            return null;
        }

        return hash('sha256', $ip.'|'.(string) config('app.key'));
    }

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
            $expanded = substr($expanded, 0, 6).str_repeat("\0", 10);

            return inet_ntop($expanded) ?: null;
        }

        return null;
    }
}
