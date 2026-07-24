@extends('layouts.account')

@section('account')
    <div class="mb-4">
        <a href="{{ route('account.campaigns.index') }}" class="small text-muted-custom text-decoration-none">&larr; Campaigns</a>
        <h1 class="h3 mb-1 mt-1">{{ $campaign->name }}</h1>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6"><div class="account-stat card-surface p-3"><div class="stat-label">QRs</div><div class="stat-value">{{ $report['qr_count'] }}</div></div></div>
        <div class="col-6"><div class="account-stat card-surface p-3"><div class="stat-label">Scans</div><div class="stat-value">{{ number_format($report['total_scans']) }}</div></div></div>
    </div>

    <div class="card-surface p-3 p-md-4 mb-4">
        <h2 class="h6 mb-3">Daily scans</h2>
        <canvas id="campaignDaily" height="110"></canvas>
    </div>

    <div class="row g-3">
        @foreach(['countries'=>'Countries','devices'=>'Devices','browsers'=>'Browsers'] as $key => $label)
            <div class="col-md-4">
                <div class="card-surface p-3">
                    <h3 class="h6">{{ $label }}</h3>
                    @forelse($report[$key] as $row)
                        <div class="d-flex justify-content-between small py-1 border-bottom"><span>{{ $row['label'] }}</span><strong>{{ $row['scans'] }}</strong></div>
                    @empty
                        <p class="small text-muted-custom mb-0">No data.</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const daily = @json($report['daily']);
new Chart(document.getElementById('campaignDaily'), {
  type: 'line',
  data: { labels: daily.map(d => d.date), datasets: [{ data: daily.map(d => d.scans), borderColor: '#0B6E4F', tension: .25, fill: false }] },
  options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
</script>
@endpush
