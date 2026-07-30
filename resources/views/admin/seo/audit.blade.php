@extends('layouts.admin')

@section('title', 'SEO Audit')
@section('page-title', 'SEO Audit')

@push('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">SEO Audit</li>
@endpush

@section('content')
    @php $s = $report['summary']; @endphp

    <div class="row">
        <div class="col-lg-3 col-6"><div class="small-box bg-info"><div class="inner"><h3>{{ number_format($s['calculators']) }}</h3><p>Active Calculators</p></div></div></div>
        <div class="col-lg-3 col-6"><div class="small-box bg-success"><div class="inner"><h3>{{ number_format($s['blog_posts']) }}</h3><p>Published Posts</p></div></div></div>
        <div class="col-lg-3 col-6"><div class="small-box bg-warning"><div class="inner"><h3>{{ number_format($s['missing_titles']) }}</h3><p>Missing Meta Titles</p></div></div></div>
        <div class="col-lg-3 col-6"><div class="small-box bg-danger"><div class="inner"><h3>{{ number_format($s['duplicate_titles']) }}</h3><p>Duplicate Titles</p></div></div></div>
    </div>

    <div class="row">
        <div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-warning"><i class="fas fa-align-left"></i></span><div class="info-box-content"><span class="info-box-text">Missing Descriptions</span><span class="info-box-number">{{ $s['missing_descriptions'] }}</span></div></div></div>
        <div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-secondary"><i class="fas fa-file-alt"></i></span><div class="info-box-content"><span class="info-box-text">Thin Calculator Pages</span><span class="info-box-number">{{ $s['thin_calculators'] }}</span></div></div></div>
        <div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-primary"><i class="fas fa-question-circle"></i></span><div class="info-box-content"><span class="info-box-text">Calculators Without FAQs</span><span class="info-box-number">{{ $s['calculators_without_faqs'] }}</span></div></div></div>
    </div>

    <p class="text-muted small">
        Fix missing titles/descriptions in Calculators / Blog / Categories.
        Run <code>php artisan calculators:enrich-content</code> for thin calculator copy.
        Manage redirects at <a href="{{ route('admin.seo.redirects') }}">SEO Redirects</a>.
    </p>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-warning">
                <div class="card-header"><h3 class="card-title">Missing Meta Titles</h3></div>
                <div class="card-body p-0" style="max-height:360px;overflow:auto;">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Type</th><th>Title</th></tr></thead>
                        <tbody>
                        @forelse($report['missing_titles'] as $row)
                            <tr><td>{{ $row['type'] }}</td><td><a href="{{ $row['url'] }}" target="_blank">{{ $row['title'] }}</a></td></tr>
                        @empty
                            <tr><td colspan="2" class="text-muted p-3">None — good.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-outline card-danger">
                <div class="card-header"><h3 class="card-title">Duplicate Titles</h3></div>
                <div class="card-body p-0" style="max-height:360px;overflow:auto;">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Title</th><th>Count</th></tr></thead>
                        <tbody>
                        @forelse($report['duplicate_titles'] as $row)
                            <tr>
                                <td>{{ \Illuminate\Support\Str::limit($row['title'], 60) }}</td>
                                <td>{{ $row['count'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-muted p-3">None — good.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-secondary">
        <div class="card-header"><h3 class="card-title">Thin Calculator Content (sample)</h3></div>
        <div class="card-body p-0" style="max-height:320px;overflow:auto;">
            <ul class="list-group list-group-flush">
                @forelse($report['thin_calculators'] as $row)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ $row['title'] }}</span>
                        <a href="{{ $row['url'] }}" target="_blank" class="small">Open</a>
                    </li>
                @empty
                    <li class="list-group-item text-muted">No thin pages flagged.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
