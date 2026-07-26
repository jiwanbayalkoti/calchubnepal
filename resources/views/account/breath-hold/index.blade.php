@extends('layouts.account')

@section('account')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Breath Hold certificates</h1>
            <p class="text-muted-custom mb-0">Your saved Breath Hold Test results and downloadable certificates.</p>
        </div>
        <a href="{{ route('home') }}#breath-hold" class="btn btn-sm btn-brand">
            <i class="bi bi-lungs"></i> Play again
        </a>
    </div>

    <div class="card-surface p-0 overflow-hidden">
        @forelse ($results as $item)
            <div class="account-list-item px-3 px-md-4">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <span class="calc-icon" style="width:40px;height:40px;"><i class="bi bi-lungs"></i></span>
                    <div class="min-w-0 flex-grow-1">
                        <div class="fw-semibold">
                            {{ $item->formattedDuration() }}
                            <span class="badge ms-1
                                @if($item->band === 'poor') text-bg-danger
                                @elseif($item->band === 'medium') text-bg-warning
                                @else text-bg-success
                                @endif">{{ $item->bandLabel() }}</span>
                        </div>
                        <div class="small text-muted-custom">
                            {{ $item->created_at?->format('M j, Y g:i A') }}
                            @if ($item->certificate_code)
                                · <code>{{ $item->certificate_code }}</code>
                            @endif
                        </div>
                    </div>
                    @if ($item->hasCertificate())
                        <div class="d-flex gap-2">
                            <a href="{{ route('account.breath-hold.show', $item) }}"
                               class="btn btn-sm btn-brand js-breath-cert-view"
                               data-cert-url="{{ route('account.breath-hold.show', $item) }}">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <a href="{{ route('account.breath-hold.download', $item) }}" class="btn btn-sm btn-outline-brand">
                                <i class="bi bi-download"></i> Download
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-4 text-muted-custom">
                No Breath Hold results yet.
                <a href="{{ route('home') }}#breath-hold">Take the test</a> and claim a certificate after sign up.
            </div>
        @endforelse
    </div>

    <div class="mt-3">{{ $results->links() }}</div>
@endsection
