@extends('layouts.advertiser')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('breadcrumbs')
    <li class="breadcrumb-item active">Dashboard</li>
@endpush

@section('content')
    <div class="row">
        <div class="col-lg-2 col-6">
            <div class="small-box bg-info"><div class="inner"><h3>{{ $activeAds }}</h3><p>Active Ads</p></div><div class="icon"><i class="fas fa-bullhorn"></i></div></div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-primary"><div class="inner"><h3>{{ number_format($totalImpressions) }}</h3><p>Impressions</p></div><div class="icon"><i class="fas fa-eye"></i></div></div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-success"><div class="inner"><h3>{{ number_format($totalClicks) }}</h3><p>Clicks</p></div><div class="icon"><i class="fas fa-mouse-pointer"></i></div></div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-warning"><div class="inner"><h3>{{ $ctr }}%</h3><p>CTR</p></div><div class="icon"><i class="fas fa-percentage"></i></div></div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-secondary"><div class="inner"><h3>{{ $runningDays }}</h3><p>Running Days</p></div><div class="icon"><i class="fas fa-calendar-check"></i></div></div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-dark"><div class="inner"><h3>{{ $remainingDays ?? '∞' }}</h3><p>Remaining Days</p></div><div class="icon"><i class="fas fa-hourglass-half"></i></div></div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Performance · Last 7 Days</h3></div>
                <div class="card-body"><canvas id="dashChart" height="120"></canvas></div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Recent Clicks</h3></div>
                <div class="card-body p-0" style="max-height:280px;overflow-y:auto;">
                    <ul class="list-group list-group-flush">
                        @forelse ($recentClicks as $click)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $click->advertisement?->name ?? 'Ad' }}</span>
                                <small class="text-muted">{{ $click->clicked_at?->diffForHumans() }}</small>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">No clicks yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h3 class="card-title">Recent Impressions</h3></div>
                <div class="card-body p-0" style="max-height:280px;overflow-y:auto;">
                    <ul class="list-group list-group-flush">
                        @forelse ($recentImpressions as $imp)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $imp->advertisement?->name ?? 'Ad' }}</span>
                                <small class="text-muted">{{ $imp->viewed_at?->diffForHumans() }}</small>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">No impressions yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('dashChart'), {
    type: 'line',
    data: {
        labels: @json($series['labels']),
        datasets: [
            { label: 'Impressions', data: @json($series['impressions']), borderColor: '#1e5bb8', tension: .3 },
            { label: 'Clicks', data: @json($series['clicks']), borderColor: '#28a745', tension: .3 },
        ]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
</script>
@endpush
