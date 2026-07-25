@extends('layouts.admin')

@section('title', 'Analytics')
@section('page-title', 'Analytics')

@push('breadcrumbs')
    <li class="breadcrumb-item active">Analytics</li>
@endpush

@section('content')
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ number_format($pageViewsSummary['today']) }}</h3>
                    <p>Page Views Today</p>
                </div>
                <div class="icon"><i class="fas fa-eye"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($pageViewsSummary['this_week']) }}</h3>
                    <p>Page Views · 7 Days</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-week"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($pageViewsSummary['this_month']) }}</h3>
                    <p>Page Views · 30 Days</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-alt"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ number_format($pageViewsSummary['total']) }}</h3>
                    <p>Total Page Views</p>
                </div>
                <div class="icon"><i class="fas fa-infinity"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-olive">
                <div class="inner">
                    <h3>{{ number_format($siteViewsSummary['today']) }}</h3>
                    <p>Site Views Today</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-lightblue">
                <div class="inner">
                    <h3>{{ number_format($siteViewsSummary['this_week']) }}</h3>
                    <p>Site Views · 7 Days</p>
                </div>
                <div class="icon"><i class="fas fa-user-friends"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-pink">
                <div class="inner">
                    <h3>{{ number_format($siteViewsSummary['this_month']) }}</h3>
                    <p>Site Views · 30 Days</p>
                </div>
                <div class="icon"><i class="fas fa-globe"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-navy">
                <div class="inner">
                    <h3>{{ number_format($siteViewsSummary['total']) }}</h3>
                    <p>Total Site Views</p>
                </div>
                <div class="icon"><i class="fas fa-user-check"></i></div>
            </div>
        </div>
    </div>
    <p class="text-muted small mb-3 mt-n2">
        <strong>Page views</strong> = each page hit.
        <strong>Site views</strong> = unique visitors (session / IP) in that period.
    </p>

    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($usageSummary['calculations_today']) }}</h3>
                    <p>Calculations Today</p>
                </div>
                <div class="icon"><i class="fas fa-calculator"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-teal">
                <div class="inner">
                    <h3>{{ number_format($usageSummary['calculations_week']) }}</h3>
                    <p>Calculations · 7 Days</p>
                </div>
                <div class="icon"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3>{{ number_format($usageSummary['ai_today']) }}</h3>
                    <p>AI Requests Today</p>
                </div>
                <div class="icon"><i class="fas fa-robot"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-dark">
                <div class="inner">
                    <h3>{{ number_format($usageSummary['ai_total']) }}</h3>
                    <p>AI Requests Total</p>
                </div>
                <div class="icon"><i class="fas fa-microchip"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card card-primary card-outline">
                <div class="card-header"><h3 class="card-title">Traffic &amp; Usage · Last 14 Days</h3></div>
                <div class="card-body">
                    <canvas id="pageViewsChart" style="min-height:300px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card card-success card-outline">
                <div class="card-header"><h3 class="card-title">Most Popular Calculators</h3></div>
                <div class="card-body p-0" style="max-height:360px; overflow-y:auto;">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr><th>Calculator</th><th>Uses</th><th>Views</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($popularCalculators as $calculator)
                                <tr>
                                    <td>
                                        <a href="{{ route('calculators.show', $calculator) }}" target="_blank" rel="noopener">
                                            {{ $calculator->title }}
                                        </a>
                                    </td>
                                    <td>{{ number_format($calculator->usage_count) }}</td>
                                    <td>{{ number_format($calculator->views_count) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted">No usage data yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @php
        $countryMapValues = $countryRows
            ->filter(fn ($row) => preg_match('/^[A-Z]{2}$/', (string) $row->code))
            ->mapWithKeys(fn ($row) => [$row->code => (int) $row->views])
            ->all();
        $countryMapMeta = $countryRows
            ->filter(fn ($row) => preg_match('/^[A-Z]{2}$/', (string) $row->code))
            ->mapWithKeys(fn ($row) => [$row->code => [
                'name' => $row->name,
                'views' => (int) $row->views,
                'visitors' => (int) $row->visitors,
                'share' => (float) $row->share,
            ]])
            ->all();
        $countryList = $countryRows->take(8);
        $countryMaxViews = max(1, (int) ($countryList->first()->views ?? 1));
        $pageList = $popularPages->take(8);
        $pageMaxViews = max(1, (int) ($pageList->first()->views ?? 1));
        $calcMaxUses = max(1, (int) ($popularCalculators->first()->usage_count ?? 1));
    @endphp

    <div class="d-flex align-items-center justify-content-between mb-2 mt-1">
        <h5 class="mb-0 font-weight-bold">Insights</h5>
        <span class="text-muted small">Google Analytics–style cards · last 30 days unless noted</span>
    </div>

    <div class="row ga-insights">
        {{-- 1. Country --}}
        <div class="col-xl-4 col-lg-6 mb-3">
            <div class="card ga-card h-100">
                <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-start">
                    <div>
                        <div class="ga-card-title">Page views by Country</div>
                        <div class="ga-card-sub">Last 30 days · {{ number_format($countryKnownShare, 1) }}% geo-tagged</div>
                    </div>
                    <span class="ga-status {{ empty($countryMapValues) ? 'is-warn' : 'is-ok' }}" title="{{ empty($countryMapValues) ? 'No geo data yet' : 'Data available' }}">
                        <i class="fas {{ empty($countryMapValues) ? 'fa-exclamation-triangle' : 'fa-check' }}"></i>
                    </span>
                </div>
                <div class="card-body pt-2">
                    <div id="countryViewsMap" class="ga-map" aria-label="World map of page views by country"></div>
                    <div class="ga-list-head mt-2">
                        <span>Country</span>
                        <span>Page views</span>
                    </div>
                    <div class="ga-list">
                        @forelse ($countryList as $row)
                            <div class="ga-list-row {{ $row->code !== '—' ? 'js-country-row' : '' }}"
                                 @if($row->code !== '—') data-country="{{ $row->code }}" @endif>
                                <div class="d-flex justify-content-between">
                                    <span>
                                        <span class="mr-1">{{ $row->flag }}</span>{{ $row->name }}
                                    </span>
                                    <strong>{{ number_format($row->views) }}</strong>
                                </div>
                                <div class="progress ga-bar">
                                    <div class="progress-bar" style="width: {{ min(100, round(($row->views / $countryMaxViews) * 100)) }}%;"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">No country data yet.</p>
                        @endforelse
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <span class="ga-link">View countries <i class="fas fa-arrow-right"></i></span>
                </div>
            </div>
        </div>

        {{-- 2. Realtime --}}
        <div class="col-xl-4 col-lg-6 mb-3">
            <div class="card ga-card h-100">
                <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-start">
                    <div>
                        <div class="ga-card-title">Active users in last 30 minutes</div>
                        <div class="ga-card-sub">Realtime · unique visitors</div>
                    </div>
                    <span class="ga-status is-ok"><i class="fas fa-check"></i></span>
                </div>
                <div class="card-body pt-2">
                    <div class="ga-metric">{{ number_format($realtimeActiveUsers) }}</div>
                    <div class="text-muted small mb-2">{{ number_format($realtimePageViews) }} page views in window</div>
                    <div class="text-uppercase text-muted small font-weight-bold mb-1">Active users per minute</div>
                    <div class="ga-chart-wrap mb-3">
                        <canvas id="realtimeMinuteChart" height="90"></canvas>
                    </div>
                    <div class="ga-list-head">
                        <span>Top countries</span>
                        <span>Users</span>
                    </div>
                    <div class="ga-list">
                        @forelse ($realtimeTopCountries as $row)
                            <div class="ga-list-row">
                                <div class="d-flex justify-content-between">
                                    <span><span class="mr-1">{{ $row->flag }}</span>{{ $row->name }}</span>
                                    <strong>{{ number_format($row->visitors) }}</strong>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">No active users in the last 30 minutes.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Top pages (city substitute) --}}
        <div class="col-xl-4 col-lg-6 mb-3">
            <div class="card ga-card h-100">
                <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-start">
                    <div>
                        <div class="ga-card-title">Page views by Page</div>
                        <div class="ga-card-sub">Top pages · last 30 days</div>
                    </div>
                    <span class="ga-status {{ $pageList->isEmpty() ? 'is-warn' : 'is-ok' }}">
                        <i class="fas {{ $pageList->isEmpty() ? 'fa-exclamation-triangle' : 'fa-check' }}"></i>
                    </span>
                </div>
                <div class="card-body pt-2">
                    <div class="ga-list-head">
                        <span>Page</span>
                        <span>Views</span>
                    </div>
                    <div class="ga-list ga-list--tall">
                        @forelse ($pageList as $page)
                            <div class="ga-list-row">
                                <div class="d-flex justify-content-between">
                                    <span class="ga-ellipsis" title="{{ $page->title }}">{{ $page->title }}</span>
                                    <strong class="ml-2">{{ number_format($page->views) }}</strong>
                                </div>
                                <div class="progress ga-bar">
                                    <div class="progress-bar" style="width: {{ min(100, round(($page->views / $pageMaxViews) * 100)) }}%;"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">No page data yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Devices (gender substitute) --}}
        <div class="col-xl-4 col-lg-6 mb-3">
            <div class="card ga-card h-100">
                <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-start">
                    <div>
                        <div class="ga-card-title">Page views by Device</div>
                        <div class="ga-card-sub">Desktop / mobile / tablet</div>
                    </div>
                    <span class="ga-status {{ $deviceRows->isEmpty() ? 'is-warn' : 'is-ok' }}">
                        <i class="fas {{ $deviceRows->isEmpty() ? 'fa-exclamation-triangle' : 'fa-check' }}"></i>
                    </span>
                </div>
                <div class="card-body pt-2">
                    @if ($deviceRows->isEmpty())
                        <div class="ga-empty">No data available</div>
                    @else
                        <div class="ga-chart-wrap ga-chart-donut mb-3">
                            <canvas id="deviceDonutChart"></canvas>
                        </div>
                        <div class="ga-list">
                            @foreach ($deviceRows as $row)
                                <div class="ga-list-row">
                                    <div class="d-flex justify-content-between">
                                        <span>{{ $row->label }}</span>
                                        <strong>{{ number_format($row->views) }} <span class="text-muted font-weight-normal">({{ $row->share }}%)</span></strong>
                                    </div>
                                    <div class="progress ga-bar">
                                        <div class="progress-bar" style="width: {{ min(100, $row->share) }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 5. Referrers (interests substitute) --}}
        <div class="col-xl-4 col-lg-6 mb-3">
            <div class="card ga-card h-100">
                <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-start">
                    <div>
                        <div class="ga-card-title">Page views by Referrer</div>
                        <div class="ga-card-sub">Traffic sources · last 30 days</div>
                    </div>
                    <span class="ga-status {{ $referrerRows->isEmpty() ? 'is-warn' : 'is-ok' }}">
                        <i class="fas {{ $referrerRows->isEmpty() ? 'fa-exclamation-triangle' : 'fa-check' }}"></i>
                    </span>
                </div>
                <div class="card-body pt-2">
                    @if ($referrerRows->isEmpty())
                        <div class="ga-empty">No data available</div>
                    @else
                        <div class="ga-list-head">
                            <span>Source</span>
                            <span>Views</span>
                        </div>
                        <div class="ga-list ga-list--tall">
                            @foreach ($referrerRows as $row)
                                <div class="ga-list-row">
                                    <div class="d-flex justify-content-between">
                                        <span class="ga-ellipsis" title="{{ $row->host }}">{{ $row->host }}</span>
                                        <strong class="ml-2">{{ number_format($row->views) }}</strong>
                                    </div>
                                    <div class="progress ga-bar">
                                        <div class="progress-bar" style="width: {{ min(100, round(($row->views / $referrerMax) * 100)) }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 6. Popular calculators --}}
        <div class="col-xl-4 col-lg-6 mb-3">
            <div class="card ga-card h-100">
                <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-start">
                    <div>
                        <div class="ga-card-title">Top calculators</div>
                        <div class="ga-card-sub">By lifetime uses</div>
                    </div>
                    <span class="ga-status {{ $popularCalculators->isEmpty() ? 'is-warn' : 'is-ok' }}">
                        <i class="fas {{ $popularCalculators->isEmpty() ? 'fa-exclamation-triangle' : 'fa-check' }}"></i>
                    </span>
                </div>
                <div class="card-body pt-2">
                    <div class="ga-list ga-list--tall">
                        @forelse ($popularCalculators->take(8) as $calculator)
                            <div class="ga-list-row">
                                <div class="d-flex justify-content-between">
                                    <a class="ga-ellipsis" href="{{ route('calculators.show', $calculator) }}" target="_blank" rel="noopener">{{ $calculator->title }}</a>
                                    <strong class="ml-2">{{ number_format($calculator->usage_count) }}</strong>
                                </div>
                                <div class="progress ga-bar">
                                    <div class="progress-bar" style="width: {{ min(100, round(($calculator->usage_count / $calcMaxUses) * 100)) }}%;"></div>
                                </div>
                            </div>
                        @empty
                            <div class="ga-empty">No data available</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-3">
            <div class="card ga-card">
                <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-start">
                    <div>
                        <div class="ga-card-title">Recent visits</div>
                        <div class="ga-card-sub">Last 7 days · IP truncated · admin only</div>
                    </div>
                    <span class="ga-status is-ok"><i class="fas fa-check"></i></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height:320px; overflow-y:auto;">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>When</th>
                                    <th>Path</th>
                                    <th>Country</th>
                                    <th>Device</th>
                                    <th>IP (truncated)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentVisits as $visit)
                                    <tr>
                                        <td class="text-nowrap">{{ $visit->created_at?->format('M d H:i') }}</td>
                                        <td><code class="small">{{ \Illuminate\Support\Str::limit($visit->path, 40) }}</code></td>
                                        <td>
                                            @if($visit->country)
                                                <span class="badge badge-light border">{{ strtoupper($visit->country) }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-capitalize">{{ $visit->device ?: '—' }}</td>
                                        <td><code class="small">{{ $visit->ip_truncated ?: '—' }}</code></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-muted p-3">No recent visits recorded.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css">
<style>
    .ga-insights .ga-card {
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }
    .ga-card .card-header {
        background: transparent;
    }
    .ga-card-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #111827;
        line-height: 1.3;
    }
    .ga-card-sub {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 0.15rem;
    }
    .ga-status {
        width: 22px;
        height: 22px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.65rem;
        flex-shrink: 0;
    }
    .ga-status.is-ok {
        background: #dcfce7;
        color: #15803d;
    }
    .ga-status.is-warn {
        background: #ffedd5;
        color: #c2410c;
    }
    .ga-metric {
        font-size: 2.4rem;
        font-weight: 700;
        line-height: 1;
        color: #111827;
        letter-spacing: -0.03em;
    }
    .ga-map {
        height: 180px;
        width: 100%;
        border: 1px solid #eef2f7;
        border-radius: 0.5rem;
        background: #f8fafc;
        overflow: hidden;
    }
    .ga-list-head {
        display: flex;
        justify-content: space-between;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-size: 0.68rem;
        font-weight: 700;
        color: #6b7280;
        margin-bottom: 0.35rem;
        padding: 0 0.15rem;
    }
    .ga-list {
        max-height: 180px;
        overflow-y: auto;
    }
    .ga-list--tall {
        max-height: 280px;
    }
    .ga-list-row {
        padding: 0.45rem 0.15rem;
        border-bottom: 1px solid #f3f4f6;
    }
    .ga-list-row.js-country-row {
        cursor: pointer;
    }
    .ga-list-row.js-country-row:hover,
    .ga-list-row.is-active {
        background: rgba(37, 99, 235, 0.06);
        border-radius: 0.35rem;
    }
    .ga-bar {
        height: 4px;
        margin-top: 0.3rem;
        background: #eef2ff;
        border-radius: 999px;
    }
    .ga-bar .progress-bar {
        background: #2563eb;
        border-radius: 999px;
    }
    .ga-ellipsis {
        display: inline-block;
        max-width: 70%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: bottom;
        color: inherit;
    }
    .ga-link {
        color: #2563eb;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .ga-empty {
        min-height: 160px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 0.9rem;
        border: 1px dashed #e5e7eb;
        border-radius: 0.5rem;
        background: #fafafa;
    }
    .ga-chart-wrap {
        position: relative;
        width: 100%;
    }
    .ga-chart-donut {
        max-width: 180px;
        margin: 0 auto;
        height: 160px;
    }
    .jvm-zoom-btn {
        border-color: #cbd5e1 !important;
        background: #fff !important;
        color: #334155 !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/js/jsvectormap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/maps/world.js"></script>
<script>
$(function () {
    $.get('{{ route("admin.analytics.page-views-chart") }}', function (res) {
        new Chart(document.getElementById('pageViewsChart'), {
            type: 'bar',
            data: {
                labels: res.labels,
                datasets: [
                    {
                        label: 'Page Views',
                        data: res.page_views || res.data || [],
                        backgroundColor: 'rgba(40, 167, 69, 0.75)',
                    },
                    {
                        label: 'Site Views',
                        data: res.site_views || [],
                        backgroundColor: 'rgba(255, 193, 7, 0.75)',
                    },
                    {
                        label: 'Calculations',
                        data: res.calculations || [],
                        backgroundColor: 'rgba(0, 123, 255, 0.65)',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            },
        });
    });

    const realtimeLabels = @json($realtimeMinuteLabels);
    const realtimeData = @json($realtimePerMinute);
    const realtimeCanvas = document.getElementById('realtimeMinuteChart');
    if (realtimeCanvas) {
        new Chart(realtimeCanvas, {
            type: 'bar',
            data: {
                labels: realtimeLabels,
                datasets: [{
                    data: realtimeData,
                    backgroundColor: '#2563eb',
                    borderRadius: 2,
                    barPercentage: 0.8,
                    categoryPercentage: 0.9,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { enabled: true } },
                scales: {
                    x: { display: false },
                    y: { display: false, beginAtZero: true },
                },
            },
        });
    }

    const deviceRows = @json($deviceRows);
    const deviceCanvas = document.getElementById('deviceDonutChart');
    if (deviceCanvas && deviceRows.length) {
        new Chart(deviceCanvas, {
            type: 'doughnut',
            data: {
                labels: deviceRows.map((r) => r.label),
                datasets: [{
                    data: deviceRows.map((r) => r.views),
                    backgroundColor: ['#2563eb', '#60a5fa', '#93c5fd', '#cbd5e1'],
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: { legend: { display: false } },
            },
        });
    }

    const countryValues = @json($countryMapValues);
    const countryMeta = @json($countryMapMeta);
    const mapEl = document.getElementById('countryViewsMap');

    if (mapEl && typeof jsVectorMap === 'function') {
        const map = new jsVectorMap({
            selector: '#countryViewsMap',
            map: 'world',
            backgroundColor: 'transparent',
            draggable: true,
            zoomButtons: true,
            zoomOnScroll: false,
            regionStyle: {
                initial: { fill: '#e2e8f0', stroke: '#ffffff', strokeWidth: 0.5 },
                hover: { fill: '#93c5fd', cursor: 'pointer' },
                selected: { fill: '#1d4ed8' },
            },
            visualizeData: {
                scale: ['#bfdbfe', '#1e40af'],
                values: countryValues,
            },
            onRegionTooltipShow(event, tooltip, code) {
                const meta = countryMeta[code];
                if (!meta) {
                    tooltip.text(code);
                    return;
                }
                tooltip.text(
                    meta.name + ': ' + meta.views.toLocaleString() + ' views · ' +
                    meta.visitors.toLocaleString() + ' site views (' + meta.share + '%)'
                );
            },
            onRegionClick(event, code) {
                const row = document.querySelector('.js-country-row[data-country="' + code + '"]');
                if (!row) return;
                document.querySelectorAll('.js-country-row.is-active').forEach((el) => el.classList.remove('is-active'));
                row.classList.add('is-active');
                row.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            },
        });

        $(document).on('mouseenter', '.js-country-row', function () {
            const code = this.getAttribute('data-country');
            if (!code || !map) return;
            try { map.setSelectedRegions([code]); } catch (e) {}
        });

        $(document).on('mouseleave', '.js-country-row', function () {
            if (!map) return;
            try { map.clearSelectedRegions(); } catch (e) {}
        });
    }
});
</script>
@endpush
