@extends('layouts.account')

@section('account')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
        <div>
            <nav class="small text-muted-custom mb-2">
                <a href="{{ route('account.breath-hold.index') }}" class="text-decoration-none">Breath Hold</a>
                <span class="mx-1">/</span>
                <span>{{ $result->certificate_code }}</span>
            </nav>
            <h1 class="h3 mb-1">Your certificate</h1>
            <p class="text-muted-custom mb-0">
                {{ $result->funnyTitle() }} · {{ $result->formattedDuration() }} · {{ $result->bandLabel() }}
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('account.breath-hold.index') }}" class="btn btn-sm btn-outline-brand">
                <i class="bi bi-arrow-left"></i> All results
            </a>
            <a href="{{ $downloadUrl }}" class="btn btn-sm btn-brand" download>
                <i class="bi bi-download"></i> Download PNG
            </a>
        </div>
    </div>

    <div class="card-surface p-3 p-md-4 mb-3">
        <div class="breath-cert-preview text-center">
            <img
                src="{{ $imageUrl }}"
                alt="Breath Hold certificate {{ $result->certificate_code }}"
                class="breath-cert-preview__img img-fluid rounded border"
            >
        </div>
    </div>

    <div class="card-surface p-3 p-md-4">
        <div class="row g-3 small">
            <div class="col-sm-6 col-md-3">
                <div class="text-muted-custom">Hold time</div>
                <div class="fw-semibold">{{ $result->formattedDuration() }}</div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="text-muted-custom">Band</div>
                <div class="fw-semibold">{{ $result->bandLabel() }} ({{ $result->bandRangeLabel() }})</div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="text-muted-custom">Certificate ID</div>
                <div class="fw-semibold"><code>{{ $result->certificate_code }}</code></div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="text-muted-custom">Played</div>
                <div class="fw-semibold">{{ $result->created_at?->format('M j, Y g:i A') }}</div>
            </div>
        </div>
        <p class="text-muted-custom small mb-0 mt-3">{{ $result->funnySubtitle() }}</p>
    </div>
@endsection

@push('styles')
<style>
    .breath-cert-preview__img {
        max-width: 100%;
        height: auto;
        box-shadow: 0 12px 40px rgba(16, 24, 40, 0.12);
        background: #fffef8;
    }
</style>
@endpush
