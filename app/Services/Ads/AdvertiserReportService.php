<?php

namespace App\Services\Ads;

use App\Models\AdsenseImpression;
use App\Models\Advertisement;
use App\Models\AdvertisementClick;
use App\Models\AdvertisementImpression;
use App\Models\Advertiser;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class AdvertiserReportService
{
    /**
     * @return array{from: Carbon, to: Carbon, label: string}
     */
    public function resolveRange(string $preset, ?string $from = null, ?string $to = null): array
    {
        $today = Carbon::today();

        return match ($preset) {
            'today' => ['from' => $today->copy()->startOfDay(), 'to' => $today->copy()->endOfDay(), 'label' => 'Today'],
            'yesterday' => [
                'from' => $today->copy()->subDay()->startOfDay(),
                'to' => $today->copy()->subDay()->endOfDay(),
                'label' => 'Yesterday',
            ],
            'last_7' => ['from' => $today->copy()->subDays(6)->startOfDay(), 'to' => $today->copy()->endOfDay(), 'label' => 'Last 7 Days'],
            'this_month' => ['from' => $today->copy()->startOfMonth(), 'to' => $today->copy()->endOfDay(), 'label' => 'This Month'],
            'last_month' => [
                'from' => $today->copy()->subMonthNoOverflow()->startOfMonth(),
                'to' => $today->copy()->subMonthNoOverflow()->endOfMonth()->endOfDay(),
                'label' => 'Last Month',
            ],
            'custom' => [
                'from' => Carbon::parse($from ?: $today->copy()->subDays(29))->startOfDay(),
                'to' => Carbon::parse($to ?: $today)->endOfDay(),
                'label' => 'Custom',
            ],
            default => ['from' => $today->copy()->subDays(29)->startOfDay(), 'to' => $today->copy()->endOfDay(), 'label' => 'Last 30 Days'],
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(Advertiser $advertiser, Carbon $from, Carbon $to): array
    {
        $impressions = AdvertisementImpression::query()
            ->where('advertiser_id', $advertiser->id)
            ->whereBetween('viewed_at', [$from, $to])
            ->count();

        $clicks = AdvertisementClick::query()
            ->where('advertiser_id', $advertiser->id)
            ->whereBetween('clicked_at', [$from, $to])
            ->count();

        $days = max(1, (int) $from->diffInDays($to) + 1);
        $ctr = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0.0;

        return [
            'impressions' => $impressions,
            'clicks' => $clicks,
            'ctr' => $ctr,
            'avg_daily_impressions' => round($impressions / $days, 1),
            'avg_daily_clicks' => round($clicks / $days, 1),
            'days' => $days,
        ];
    }

    /**
     * @return array{labels: list<string>, impressions: list<int>, clicks: list<int>, ctr: list<float>}
     */
    public function dailySeries(Advertiser $advertiser, Carbon $from, Carbon $to): array
    {
        $labels = [];
        $impressions = [];
        $clicks = [];
        $ctr = [];

        $impMap = AdvertisementImpression::query()
            ->selectRaw('DATE(viewed_at) as d, COUNT(*) as c')
            ->where('advertiser_id', $advertiser->id)
            ->whereBetween('viewed_at', [$from, $to])
            ->groupBy('d')
            ->pluck('c', 'd');

        $clickMap = AdvertisementClick::query()
            ->selectRaw('DATE(clicked_at) as d, COUNT(*) as c')
            ->where('advertiser_id', $advertiser->id)
            ->whereBetween('clicked_at', [$from, $to])
            ->groupBy('d')
            ->pluck('c', 'd');

        foreach (CarbonPeriod::create($from->copy()->startOfDay(), $to->copy()->startOfDay()) as $day) {
            $key = $day->format('Y-m-d');
            $imp = (int) ($impMap[$key] ?? 0);
            $clk = (int) ($clickMap[$key] ?? 0);
            $labels[] = $day->format('M d');
            $impressions[] = $imp;
            $clicks[] = $clk;
            $ctr[] = $imp > 0 ? round(($clk / $imp) * 100, 2) : 0.0;
        }

        return compact('labels', 'impressions', 'clicks', 'ctr');
    }

    /**
     * @return Collection<int, object>
     */
    public function dailyTable(Advertiser $advertiser, Carbon $from, Carbon $to): Collection
    {
        $ads = Advertisement::query()
            ->where('advertiser_id', $advertiser->id)
            ->pluck('name', 'id');

        $imp = AdvertisementImpression::query()
            ->selectRaw('DATE(viewed_at) as d, advertisement_id, COUNT(*) as c')
            ->where('advertiser_id', $advertiser->id)
            ->whereBetween('viewed_at', [$from, $to])
            ->groupBy('d', 'advertisement_id')
            ->get();

        $clk = AdvertisementClick::query()
            ->selectRaw('DATE(clicked_at) as d, advertisement_id, COUNT(*) as c')
            ->where('advertiser_id', $advertiser->id)
            ->whereBetween('clicked_at', [$from, $to])
            ->groupBy('d', 'advertisement_id')
            ->get()
            ->keyBy(fn ($r) => $r->d.'|'.$r->advertisement_id);

        return $imp->map(function ($row) use ($ads, $clk) {
            $key = $row->d.'|'.$row->advertisement_id;
            $clicks = (int) ($clk[$key]->c ?? 0);
            $impressions = (int) $row->c;

            return (object) [
                'date' => $row->d,
                'advertisement' => $ads[$row->advertisement_id] ?? ('#'.$row->advertisement_id),
                'advertisement_id' => (int) $row->advertisement_id,
                'impressions' => $impressions,
                'clicks' => $clicks,
                'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0.0,
            ];
        })->sortByDesc('date')->values();
    }

    /**
     * Admin-wide report filters.
     *
     * @param  array{advertiser_id?: int|null, position?: string|null}  $filters
     * @return array<string, mixed>
     */
    public function adminOverview(Carbon $from, Carbon $to, array $filters = []): array
    {
        $advertiserId = ! empty($filters['advertiser_id']) ? (int) $filters['advertiser_id'] : null;
        $position = filled($filters['position'] ?? null) ? (string) $filters['position'] : null;

        $adIds = null;
        if ($position) {
            $adIds = Advertisement::query()
                ->when($advertiserId, fn ($q) => $q->where('advertiser_id', $advertiserId))
                ->where('position', $position)
                ->pluck('id');
        }

        $impQuery = AdvertisementImpression::query()->whereBetween('viewed_at', [$from, $to]);
        $clkQuery = AdvertisementClick::query()->whereBetween('clicked_at', [$from, $to]);

        if ($advertiserId) {
            $impQuery->where('advertiser_id', $advertiserId);
            $clkQuery->where('advertiser_id', $advertiserId);
        }
        if ($adIds !== null) {
            $impQuery->whereIn('advertisement_id', $adIds);
            $clkQuery->whereIn('advertisement_id', $adIds);
        }

        $impressions = (clone $impQuery)->count();
        $clicks = (clone $clkQuery)->count();
        $days = max(1, (int) $from->diffInDays($to) + 1);
        $ctr = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0.0;

        return [
            'summary' => [
                'impressions' => $impressions,
                'clicks' => $clicks,
                'ctr' => $ctr,
                'avg_daily_impressions' => round($impressions / $days, 1),
                'avg_daily_clicks' => round($clicks / $days, 1),
                'companies' => $this->companyBreakdown($from, $to, $filters)->count(),
                'days' => $days,
            ],
            'series' => $this->adminDailySeries($from, $to, $filters),
            'by_company' => $this->companyBreakdown($from, $to, $filters),
            'by_position' => $this->positionBreakdown($from, $to, $filters),
            'table' => $this->adminDailyTable($from, $to, $filters),
        ];
    }

    /**
     * @param  array{advertiser_id?: int|null, position?: string|null}  $filters
     * @return array{labels: list<string>, impressions: list<int>, clicks: list<int>, ctr: list<float>}
     */
    public function adminDailySeries(Carbon $from, Carbon $to, array $filters = []): array
    {
        $advertiserId = ! empty($filters['advertiser_id']) ? (int) $filters['advertiser_id'] : null;
        $position = filled($filters['position'] ?? null) ? (string) $filters['position'] : null;
        $adIds = $this->filteredAdIds($advertiserId, $position);

        $impMap = AdvertisementImpression::query()
            ->selectRaw('DATE(viewed_at) as d, COUNT(*) as c')
            ->whereBetween('viewed_at', [$from, $to])
            ->when($advertiserId, fn ($q) => $q->where('advertiser_id', $advertiserId))
            ->when($adIds !== null, fn ($q) => $q->whereIn('advertisement_id', $adIds))
            ->groupBy('d')
            ->pluck('c', 'd');

        $clickMap = AdvertisementClick::query()
            ->selectRaw('DATE(clicked_at) as d, COUNT(*) as c')
            ->whereBetween('clicked_at', [$from, $to])
            ->when($advertiserId, fn ($q) => $q->where('advertiser_id', $advertiserId))
            ->when($adIds !== null, fn ($q) => $q->whereIn('advertisement_id', $adIds))
            ->groupBy('d')
            ->pluck('c', 'd');

        $labels = [];
        $impressions = [];
        $clicks = [];
        $ctr = [];

        foreach (CarbonPeriod::create($from->copy()->startOfDay(), $to->copy()->startOfDay()) as $day) {
            $key = $day->format('Y-m-d');
            $imp = (int) ($impMap[$key] ?? 0);
            $clk = (int) ($clickMap[$key] ?? 0);
            $labels[] = $day->format('M d');
            $impressions[] = $imp;
            $clicks[] = $clk;
            $ctr[] = $imp > 0 ? round(($clk / $imp) * 100, 2) : 0.0;
        }

        return compact('labels', 'impressions', 'clicks', 'ctr');
    }

    /**
     * @param  array{advertiser_id?: int|null, position?: string|null}  $filters
     * @return Collection<int, object>
     */
    public function companyBreakdown(Carbon $from, Carbon $to, array $filters = []): Collection
    {
        $position = filled($filters['position'] ?? null) ? (string) $filters['position'] : null;
        $advertiserId = ! empty($filters['advertiser_id']) ? (int) $filters['advertiser_id'] : null;
        $adIds = $this->filteredAdIds($advertiserId, $position);

        $imp = AdvertisementImpression::query()
            ->selectRaw('advertiser_id, COUNT(*) as c')
            ->whereBetween('viewed_at', [$from, $to])
            ->whereNotNull('advertiser_id')
            ->when($advertiserId, fn ($q) => $q->where('advertiser_id', $advertiserId))
            ->when($adIds !== null, fn ($q) => $q->whereIn('advertisement_id', $adIds))
            ->groupBy('advertiser_id')
            ->pluck('c', 'advertiser_id');

        $clk = AdvertisementClick::query()
            ->selectRaw('advertiser_id, COUNT(*) as c')
            ->whereBetween('clicked_at', [$from, $to])
            ->whereNotNull('advertiser_id')
            ->when($advertiserId, fn ($q) => $q->where('advertiser_id', $advertiserId))
            ->when($adIds !== null, fn ($q) => $q->whereIn('advertisement_id', $adIds))
            ->groupBy('advertiser_id')
            ->pluck('c', 'advertiser_id');

        $ids = $imp->keys()->merge($clk->keys())->unique()->values();
        $companies = Advertiser::query()->whereIn('id', $ids)->pluck('company_name', 'id');

        return $ids->map(function ($id) use ($companies, $imp, $clk) {
            $impressions = (int) ($imp[$id] ?? 0);
            $clicks = (int) ($clk[$id] ?? 0);

            return (object) [
                'advertiser_id' => (int) $id,
                'company' => $companies[$id] ?? ('#'.$id),
                'impressions' => $impressions,
                'clicks' => $clicks,
                'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0.0,
            ];
        })->sortByDesc('impressions')->values();
    }

    /**
     * Position = ad slot category (header, sidebar, …).
     *
     * @param  array{advertiser_id?: int|null, position?: string|null}  $filters
     * @return Collection<int, object>
     */
    public function positionBreakdown(Carbon $from, Carbon $to, array $filters = []): Collection
    {
        $advertiserId = ! empty($filters['advertiser_id']) ? (int) $filters['advertiser_id'] : null;
        $positionFilter = filled($filters['position'] ?? null) ? (string) $filters['position'] : null;

        $ads = Advertisement::query()
            ->when($advertiserId, fn ($q) => $q->where('advertiser_id', $advertiserId))
            ->when($positionFilter, fn ($q) => $q->where('position', $positionFilter))
            ->get(['id', 'position']);

        if ($ads->isEmpty()) {
            return collect();
        }

        $adIds = $ads->pluck('id');
        $positionByAd = $ads->pluck('position', 'id');

        $imp = AdvertisementImpression::query()
            ->selectRaw('advertisement_id, COUNT(*) as c')
            ->whereBetween('viewed_at', [$from, $to])
            ->whereIn('advertisement_id', $adIds)
            ->when($advertiserId, fn ($q) => $q->where('advertiser_id', $advertiserId))
            ->groupBy('advertisement_id')
            ->pluck('c', 'advertisement_id');

        $clk = AdvertisementClick::query()
            ->selectRaw('advertisement_id, COUNT(*) as c')
            ->whereBetween('clicked_at', [$from, $to])
            ->whereIn('advertisement_id', $adIds)
            ->when($advertiserId, fn ($q) => $q->where('advertiser_id', $advertiserId))
            ->groupBy('advertisement_id')
            ->pluck('c', 'advertisement_id');

        $byPosition = [];
        foreach ($positionByAd as $adId => $position) {
            if (! isset($byPosition[$position])) {
                $byPosition[$position] = ['impressions' => 0, 'clicks' => 0];
            }
            $byPosition[$position]['impressions'] += (int) ($imp[$adId] ?? 0);
            $byPosition[$position]['clicks'] += (int) ($clk[$adId] ?? 0);
        }

        $labels = collect(config('calculator_hub.ads.positions', []))
            ->mapWithKeys(fn ($meta, $key) => [$key => $meta['label'] ?? ucfirst($key)]);

        return collect($byPosition)->map(function ($stats, $position) use ($labels) {
            $impressions = (int) $stats['impressions'];
            $clicks = (int) $stats['clicks'];

            return (object) [
                'position' => $position,
                'label' => $labels[$position] ?? ucfirst(str_replace('_', ' ', $position)),
                'impressions' => $impressions,
                'clicks' => $clicks,
                'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0.0,
            ];
        })->sortByDesc('impressions')->values();
    }

    /**
     * @param  array{advertiser_id?: int|null, position?: string|null}  $filters
     * @return Collection<int, object>
     */
    public function adminDailyTable(Carbon $from, Carbon $to, array $filters = []): Collection
    {
        $advertiserId = ! empty($filters['advertiser_id']) ? (int) $filters['advertiser_id'] : null;
        $position = filled($filters['position'] ?? null) ? (string) $filters['position'] : null;
        $adIds = $this->filteredAdIds($advertiserId, $position);

        $ads = Advertisement::query()
            ->with('advertiser:id,company_name')
            ->when($advertiserId, fn ($q) => $q->where('advertiser_id', $advertiserId))
            ->when($position, fn ($q) => $q->where('position', $position))
            ->get(['id', 'name', 'position', 'advertiser_id'])
            ->keyBy('id');

        $imp = AdvertisementImpression::query()
            ->selectRaw('DATE(viewed_at) as d, advertisement_id, COUNT(*) as c')
            ->whereBetween('viewed_at', [$from, $to])
            ->when($advertiserId, fn ($q) => $q->where('advertiser_id', $advertiserId))
            ->when($adIds !== null, fn ($q) => $q->whereIn('advertisement_id', $adIds))
            ->groupBy('d', 'advertisement_id')
            ->get();

        $clk = AdvertisementClick::query()
            ->selectRaw('DATE(clicked_at) as d, advertisement_id, COUNT(*) as c')
            ->whereBetween('clicked_at', [$from, $to])
            ->when($advertiserId, fn ($q) => $q->where('advertiser_id', $advertiserId))
            ->when($adIds !== null, fn ($q) => $q->whereIn('advertisement_id', $adIds))
            ->groupBy('d', 'advertisement_id')
            ->get()
            ->keyBy(fn ($r) => $r->d.'|'.$r->advertisement_id);

        $positionLabels = collect(config('calculator_hub.ads.positions', []))
            ->mapWithKeys(fn ($meta, $key) => [$key => $meta['label'] ?? ucfirst($key)]);

        return $imp->map(function ($row) use ($ads, $clk, $positionLabels) {
            $key = $row->d.'|'.$row->advertisement_id;
            $ad = $ads->get($row->advertisement_id);
            $clicks = (int) ($clk[$key]->c ?? 0);
            $impressions = (int) $row->c;
            $pos = $ad?->position;

            return (object) [
                'date' => $row->d,
                'company' => $ad?->advertiser?->company_name ?? '—',
                'advertisement' => $ad?->name ?? ('#'.$row->advertisement_id),
                'position' => $pos,
                'position_label' => $pos ? ($positionLabels[$pos] ?? $pos) : '—',
                'impressions' => $impressions,
                'clicks' => $clicks,
                'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0.0,
            ];
        })->sortByDesc('date')->values();
    }

    /**
     * @return \Illuminate\Support\Collection<int, int>|null
     */
    protected function filteredAdIds(?int $advertiserId, ?string $position)
    {
        if (! $position && ! $advertiserId) {
            return null;
        }

        if (! $position) {
            return null;
        }

        return Advertisement::query()
            ->when($advertiserId, fn ($q) => $q->where('advertiser_id', $advertiserId))
            ->where('position', $position)
            ->pluck('id');
    }

    /**
     * Google AdSense local render stats (page-unit impressions).
     * Clicks stay in Google AdSense; we only count site-side unit views.
     *
     * @param  array{position?: string|null}  $filters
     * @return array<string, mixed>
     */
    public function adsenseOverview(Carbon $from, Carbon $to, array $filters = []): array
    {
        $position = filled($filters['position'] ?? null) ? (string) $filters['position'] : null;

        $base = AdsenseImpression::query()
            ->whereBetween('viewed_at', [$from, $to])
            ->when($position, fn ($q) => $q->where('position', $position));

        $impressions = (clone $base)->count();
        $days = max(1, (int) $from->diffInDays($to) + 1);

        $bySource = (clone $base)
            ->selectRaw('source, COUNT(*) as c')
            ->groupBy('source')
            ->pluck('c', 'source');

        $byPositionRaw = (clone $base)
            ->selectRaw('position, COUNT(*) as c')
            ->groupBy('position')
            ->pluck('c', 'position');

        $labels = collect(config('calculator_hub.ads.positions', []))
            ->mapWithKeys(fn ($meta, $key) => [$key => $meta['label'] ?? ucfirst($key)]);

        $byPosition = $byPositionRaw->map(function ($count, $pos) use ($labels) {
            return (object) [
                'position' => $pos,
                'label' => $labels[$pos] ?? ucfirst(str_replace('_', ' ', (string) $pos)),
                'impressions' => (int) $count,
            ];
        })->sortByDesc('impressions')->values();

        $impMap = (clone $base)
            ->selectRaw('DATE(viewed_at) as d, COUNT(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd');

        $seriesLabels = [];
        $seriesImp = [];
        foreach (CarbonPeriod::create($from->copy()->startOfDay(), $to->copy()->startOfDay()) as $day) {
            $key = $day->format('Y-m-d');
            $seriesLabels[] = $day->format('M d');
            $seriesImp[] = (int) ($impMap[$key] ?? 0);
        }

        $dailyTable = (clone $base)
            ->selectRaw('DATE(viewed_at) as d, position, source, COUNT(*) as c')
            ->groupBy('d', 'position', 'source')
            ->orderByDesc('d')
            ->limit(200)
            ->get()
            ->map(function ($row) use ($labels) {
                return (object) [
                    'date' => $row->d,
                    'position' => $row->position,
                    'position_label' => $labels[$row->position] ?? ucfirst(str_replace('_', ' ', (string) $row->position)),
                    'source' => $row->source === 'advertisement' ? 'AdSense ad row' : 'Site unit',
                    'impressions' => (int) $row->c,
                ];
            });

        // Also surface AdSense-type network ads counter totals for context.
        $adsenseAds = Advertisement::query()
            ->where(function ($q) {
                $q->where('ad_type', 'adsense')->orWhereNotNull('adsense_code');
            })
            ->when($position, fn ($q) => $q->where('position', $position))
            ->get(['id', 'name', 'position', 'impressions', 'clicks', 'advertiser_id']);

        return [
            'summary' => [
                'impressions' => $impressions,
                'avg_daily_impressions' => round($impressions / $days, 1),
                'unit_impressions' => (int) ($bySource['unit'] ?? 0),
                'advertisement_impressions' => (int) ($bySource['advertisement'] ?? 0),
                'adsense_ads_count' => $adsenseAds->count(),
                'adsense_ads_clicks' => (int) $adsenseAds->sum('clicks'),
                'note' => 'Clicks & revenue are tracked in Google AdSense. These are site-side unit impressions.',
            ],
            'series' => [
                'labels' => $seriesLabels,
                'impressions' => $seriesImp,
            ],
            'by_position' => $byPosition,
            'table' => $dailyTable,
            'ads' => $adsenseAds->map(fn (Advertisement $ad) => (object) [
                'id' => $ad->id,
                'name' => $ad->name,
                'position' => $ad->position,
                'position_label' => $labels[$ad->position] ?? $ad->position,
                'impressions' => (int) $ad->impressions,
                'clicks' => (int) $ad->clicks,
            ])->values(),
        ];
    }
}
