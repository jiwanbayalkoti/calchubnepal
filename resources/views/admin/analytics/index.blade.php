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

    <div class="row">
        <div class="col-md-7">
            <div class="card card-outline card-info">
                <div class="card-header"><h3 class="card-title">Top Paths · Last 30 Days</h3></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr><th>Path</th><th class="text-right">Views</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($topPaths as $row)
                                <tr>
                                    <td><code>{{ $row->path }}</code></td>
                                    <td class="text-right">{{ number_format($row->views) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-muted p-3">No page views recorded yet. Browse the public site, then refresh.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card card-outline card-secondary">
                <div class="card-header"><h3 class="card-title">Devices · Last 30 Days</h3></div>
                <div class="card-body">
                    @forelse ($deviceSplit as $device => $views)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-capitalize">{{ $device ?: 'unknown' }}</span>
                            <strong>{{ number_format($views) }}</strong>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No device data yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title">Countries · Last 30 Days</h3>
                </div>
                <div class="card-body p-0" style="max-height:420px; overflow-y:auto;">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Country</th>
                                <th class="text-right">Views</th>
                                <th class="text-right">Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($countryRows as $row)
                                <tr>
                                    <td>
                                        <span class="badge badge-light border mr-1">{{ $row->code }}</span>
                                        {{ $row->name }}
                                    </td>
                                    <td class="text-right">{{ number_format($row->views) }}</td>
                                    <td class="text-right">{{ $row->share }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-muted p-3">
                                        No country data yet. New visits record country from CDN headers
                                        (Cloudflare <code>CF-IPCountry</code>, etc.).
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($unknownCountryViews > 0)
                    <div class="card-footer text-muted small">
                        {{ number_format($unknownCountryViews) }} views in the last 30 days have no country
                        (local/dev or missing proxy geo header).
                    </div>
                @endif
            </div>
        </div>
        <div class="col-md-7">
            <div class="card card-outline card-dark">
                <div class="card-header">
                    <h3 class="card-title">Recent Visits · 7 Days</h3>
                    <div class="card-tools">
                        <span class="badge badge-secondary">IP truncated · admin only</span>
                    </div>
                </div>
                <div class="card-body p-0" style="max-height:420px; overflow-y:auto;">
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
                                            <span class="badge badge-light border">{{ $visit->country }}</span>
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
                <div class="card-footer text-muted small">
                    Truncated IPs (IPv4 /24) are stored server-side for abuse review only — not shared with AdSense
                    or used for ad personalization.
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
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
});
</script>
@endpush
