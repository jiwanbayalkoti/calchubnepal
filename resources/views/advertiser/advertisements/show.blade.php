@extends('layouts.advertiser')

@section('title', $ad->name)
@section('page-title', 'Advertisement Details')

@push('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('advertiser.advertisements.index') }}">My Advertisements</a></li>
    <li class="breadcrumb-item active">Details</li>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Banner Preview</h3></div>
                <div class="card-body text-center">
                    @if ($ad->image_url)
                        <img src="{{ $ad->image_url }}" class="img-fluid rounded" alt="{{ $ad->name }}">
                    @else
                        <p class="text-muted mb-0">No banner image</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h3 class="card-title">{{ $ad->name }}</h3></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Company</dt><dd class="col-sm-8">{{ $advertiser->company_name }}</dd>
                        <dt class="col-sm-4">Position</dt><dd class="col-sm-8 text-capitalize">{{ $ad->position }}</dd>
                        <dt class="col-sm-4">Size</dt><dd class="col-sm-8">{{ $ad->banner_size ?: '—' }}</dd>
                        <dt class="col-sm-4">Target URL</dt>
                        <dd class="col-sm-8">
                            @if ($ad->link_url)
                                <a href="{{ $ad->link_url }}" target="_blank" rel="noopener">{{ $ad->link_url }}</a>
                            @else —
                            @endif
                        </dd>
                        <dt class="col-sm-4">Campaign Start</dt><dd class="col-sm-8">{{ $ad->start_at?->format('M d, Y H:i') ?: '—' }}</dd>
                        <dt class="col-sm-4">Campaign End</dt><dd class="col-sm-8">{{ $ad->end_at?->format('M d, Y H:i') ?: '—' }}</dd>
                        <dt class="col-sm-4">Status</dt><dd class="col-sm-8"><span class="badge badge-info">{{ $ad->status }}</span></dd>
                        <dt class="col-sm-4">Assigned By</dt><dd class="col-sm-8">{{ $ad->assigner?->name ?? $ad->creator?->name ?? '—' }}</dd>
                        <dt class="col-sm-4">Created</dt><dd class="col-sm-8">{{ $ad->created_at?->format('M d, Y H:i') }}</dd>
                        <dt class="col-sm-4">Impressions</dt><dd class="col-sm-8">{{ number_format($ad->impressions) }}</dd>
                        <dt class="col-sm-4">Clicks</dt><dd class="col-sm-8">{{ number_format($ad->clicks) }}</dd>
                        <dt class="col-sm-4">CTR</dt><dd class="col-sm-8">{{ $ad->ctr() }}%</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
