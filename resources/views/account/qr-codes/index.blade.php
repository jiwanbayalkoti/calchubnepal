@extends('layouts.account')

@section('account')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Dynamic QR Codes</h1>
            <p class="text-muted-custom mb-0">Editable short-URL QR codes with scan analytics.</p>
        </div>
        <a href="{{ route('qr-code-generator') }}" class="btn btn-brand btn-sm">
            <i class="bi bi-plus-lg"></i> Create Dynamic QR
        </a>
    </div>

    <div class="card-surface p-3 p-md-4">
        @forelse($codes as $qr)
            <div class="account-list-item">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="qr-history-thumb" style="width:48px;height:48px;">
                        @if($qr->preview_path)
                            <img src="{{ $qr->previewUrl() }}" alt="" width="48" height="48" loading="lazy">
                        @else
                            <i class="bi bi-qr-code"></i>
                        @endif
                    </div>
                    <div class="min-w-0 flex-grow-1">
                        <a href="{{ route('account.qr-codes.show', $qr) }}" class="fw-semibold text-decoration-none d-block text-truncate">
                            {{ $qr->title ?: 'Dynamic QR' }}
                        </a>
                        <div class="small text-muted-custom">
                            <span class="badge bg-light text-dark border">{{ $qr->status?->label() ?? $qr->status }}</span>
                            · {{ number_format($qr->scan_count) }} scans
                            · <code>{{ $qr->short_code }}</code>
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <a href="{{ route('account.qr-codes.show', $qr) }}" class="btn btn-sm btn-soft">Analytics</a>
                        <a href="{{ route('account.qr-codes.edit', $qr) }}" class="btn btn-sm btn-outline-brand">Edit</a>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted-custom mb-0">No dynamic QR codes yet. Create one from the QR Generator with “Dynamic QR” enabled.</p>
        @endforelse

        <div class="mt-3">
            {{ $codes->links() }}
        </div>
    </div>
@endsection
