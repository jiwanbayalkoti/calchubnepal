@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('breadcrumbs')
    <li class="breadcrumb-item active">Dashboard</li>
@endpush

@section('content')
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($stats['users_count']) }}</h3>
                    <p>Total Users</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
                <a href="{{ route('admin.users.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($stats['calculators_count']) }}</h3>
                    <p>Calculators</p>
                </div>
                <div class="icon"><i class="fas fa-square-root-alt"></i></div>
                <a href="{{ route('admin.calculators.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($stats['usage_today']) }}</h3>
                    <p>Calculations Today</p>
                </div>
                <div class="icon"><i class="fas fa-calculator"></i></div>
                <a href="{{ route('admin.analytics.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($stats['ai_requests_count']) }}</h3>
                    <p>AI Requests</p>
                </div>
                <div class="icon"><i class="fas fa-robot"></i></div>
                <a href="{{ route('admin.ai-prompts.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Calculations - Last 7 Days</h3>
                </div>
                <div class="card-body">
                    <canvas id="usageChart" style="min-height: 300px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-secondary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Recent Activity</h3>
                </div>
                <div class="card-body p-0" style="max-height: 360px; overflow-y: auto;">
                    <ul class="list-group list-group-flush">
                        @forelse ($recentActivity as $activity)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <span><strong>{{ ucfirst($activity->action) }}</strong> in {{ str_replace('_', ' ', $activity->module) }}</span>
                                    <small class="text-muted">{{ $activity->created_at?->diffForHumans() }}</small>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">No recent activity yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        new Chart(document.getElementById('usageChart'), {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Calculations',
                    data: @json($chartData),
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0,123,255,.1)',
                    tension: 0.35,
                    fill: true,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            },
        });
    });
</script>
@endpush
