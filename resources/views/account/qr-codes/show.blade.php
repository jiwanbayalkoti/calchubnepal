@extends('layouts.account')

@section('account')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
        <div>
            <a href="{{ route('account.qr-codes.index') }}" class="small text-muted-custom text-decoration-none">&larr; All dynamic QRs</a>
            <h1 class="h3 mb-1 mt-1">{{ $qr->title ?: 'Dynamic QR' }}</h1>
            <p class="text-muted-custom mb-0">
                Short URL:
                <a href="{{ $qr->shortUrl() }}" target="_blank" rel="noopener">{{ $qr->shortUrl() }}</a>
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('account.qr-codes.edit', $qr) }}" class="btn btn-outline-brand btn-sm">Edit</a>
            @if($qr->isPaused())
                <form method="POST" action="{{ route('account.qr-codes.resume', $qr) }}">@csrf<button class="btn btn-brand btn-sm">Resume</button></form>
            @else
                <form method="POST" action="{{ route('account.qr-codes.pause', $qr) }}">@csrf<button class="btn btn-soft btn-sm">Pause</button></form>
            @endif
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3"><div class="account-stat card-surface p-3"><div class="stat-label">Total scans</div><div class="stat-value">{{ number_format($report['total']) }}</div></div></div>
        <div class="col-6 col-md-3"><div class="account-stat card-surface p-3"><div class="stat-label">Today</div><div class="stat-value">{{ number_format($report['today']) }}</div></div></div>
        <div class="col-6 col-md-3"><div class="account-stat card-surface p-3"><div class="stat-label">This week</div><div class="stat-value">{{ number_format($report['this_week']) }}</div></div></div>
        <div class="col-6 col-md-3"><div class="account-stat card-surface p-3"><div class="stat-label">This month</div><div class="stat-value">{{ number_format($report['this_month']) }}</div></div></div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card-surface p-3 p-md-4 mb-4">
                <h2 class="h5 mb-3">Daily scans (30 days)</h2>
                <canvas id="qrDailyChart" height="120"></canvas>
            </div>
            <div class="card-surface p-3 p-md-4">
                <h2 class="h5 mb-3">Monthly scans (12 months)</h2>
                <canvas id="qrMonthlyChart" height="120"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card-surface p-3 p-md-4 mb-4">
                <h2 class="h6 mb-3">Details</h2>
                <dl class="mb-0 small">
                    <dt>Status</dt><dd>{{ $qr->status?->label() }}</dd>
                    <dt>Destination</dt><dd class="text-break">{{ $qr->destination_url }}</dd>
                    <dt>Password</dt><dd>{{ $qr->isPasswordProtected() ? 'Yes' : 'No' }}</dd>
                    <dt>Expires</dt><dd>{{ $qr->expires_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') ?: 'Never' }}</dd>
                    <dt>Last scan</dt><dd>{{ $qr->last_scanned_at?->diffForHumans() ?: '—' }}</dd>
                </dl>
                @if($qr->preview_path)
                    <img src="{{ $qr->previewUrl() }}" alt="QR preview" class="img-fluid mt-3 rounded border" style="max-width:160px">
                @endif
            </div>

            @foreach([
                'Countries' => $report['countries'],
                'Devices' => $report['devices'],
                'Browsers' => $report['browsers'],
                'Operating systems' => $report['operating_systems'],
            ] as $label => $rows)
                <div class="card-surface p-3 p-md-4 mb-3">
                    <h2 class="h6 mb-2">{{ $label }}</h2>
                    @forelse($rows as $row)
                        <div class="d-flex justify-content-between small py-1 border-bottom">
                            <span>{{ $row[array_key_first($row)] }}</span>
                            <strong>{{ number_format($row['scans']) }}</strong>
                        </div>
                    @empty
                        <p class="small text-muted-custom mb-0">No data yet.</p>
                    @endforelse
                </div>
            @endforeach
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
  const daily = @json($report['daily']);
  const monthly = @json($report['monthly']);
  const brand = '#0B6E4F';

  new Chart(document.getElementById('qrDailyChart'), {
    type: 'line',
    data: {
      labels: daily.map(d => d.date),
      datasets: [{ label: 'Scans', data: daily.map(d => d.scans), borderColor: brand, tension: 0.25, fill: false }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
  });

  new Chart(document.getElementById('qrMonthlyChart'), {
    type: 'bar',
    data: {
      labels: monthly.map(d => d.month),
      datasets: [{ label: 'Scans', data: monthly.map(d => d.scans), backgroundColor: brand }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
  });
})();
</script>
@endpush
