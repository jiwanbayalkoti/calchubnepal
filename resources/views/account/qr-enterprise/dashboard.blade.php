@extends('layouts.account')

@section('account')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">QR Enterprise</h1>
            <p class="text-muted-custom mb-0">Advanced analytics, heat map and campaign overview.</p>
        </div>
        <a href="{{ route('qr-code-generator') }}" class="btn btn-brand btn-sm">Create QR</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3"><div class="account-stat card-surface p-3"><div class="stat-label">Dynamic QRs</div><div class="stat-value">{{ number_format($report['dynamic_qr_count']) }}</div></div></div>
        <div class="col-6 col-md-3"><div class="account-stat card-surface p-3"><div class="stat-label">Total scans</div><div class="stat-value">{{ number_format($report['total_scans']) }}</div></div></div>
        <div class="col-6 col-md-3"><div class="account-stat card-surface p-3"><div class="stat-label">Workspaces</div><div class="stat-value">{{ number_format($report['workspace_count']) }}</div></div></div>
        <div class="col-6 col-md-3"><div class="account-stat card-surface p-3"><div class="stat-label">Campaigns</div><div class="stat-value">{{ number_format($report['campaign_count']) }}</div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card-surface p-3 p-md-4 mb-4">
                <h2 class="h5 mb-3">Scan heat map (by country)</h2>
                <div id="qrHeatMap" class="qr-heat-map">
                    @forelse($report['heat_map'] as $point)
                        <div class="qr-heat-point" style="--lat: {{ $point['lat'] }}; --lng: {{ $point['lng'] }}; --w: {{ min(48, 12 + $point['scans']) }}px;"
                             title="{{ $point['country'] }}: {{ $point['scans'] }} scans"></div>
                    @empty
                        <p class="text-muted-custom mb-0">No geo scan data yet. Share a dynamic QR to populate the map.</p>
                    @endforelse
                </div>
                <p class="small text-muted-custom mt-2 mb-0">Points sized by scan volume using country centroids (or recorded lat/lng when available).</p>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card-surface p-3 p-md-4 mb-3">
                <h2 class="h6 mb-2">Top countries</h2>
                @forelse($report['countries'] as $row)
                    <div class="d-flex justify-content-between small py-1 border-bottom"><span>{{ $row['label'] }}</span><strong>{{ $row['scans'] }}</strong></div>
                @empty
                    <p class="small text-muted-custom mb-0">No data.</p>
                @endforelse
            </div>
            <div class="card-surface p-3 p-md-4">
                <h2 class="h6 mb-2">Devices</h2>
                @forelse($report['devices'] as $row)
                    <div class="d-flex justify-content-between small py-1 border-bottom"><span>{{ $row['label'] }}</span><strong>{{ $row['scans'] }}</strong></div>
                @empty
                    <p class="small text-muted-custom mb-0">No data.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
