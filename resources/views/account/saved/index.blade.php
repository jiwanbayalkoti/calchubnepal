@extends('layouts.account')

@section('account')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Saved calculations</h1>
            <p class="text-muted-custom mb-0">
                {{ $savedCount }} saved
                @if ($savedLimit !== null)
                    · Free plan limit {{ $savedLimit }}
                @else
                    · Unlimited
                @endif
            </p>
        </div>
        @if ($savedLimit !== null && $savedCount >= $savedLimit)
            <a href="{{ route('pricing') }}" class="btn btn-sm btn-accent">Upgrade for unlimited</a>
        @endif
    </div>

    <div class="row g-3">
        @forelse ($saved as $item)
            <div class="col-md-6">
                <div class="card-surface p-3 h-100">
                    <div class="d-flex align-items-start gap-3 mb-2">
                        <span class="calc-icon" style="width:40px;height:40px;"><i class="bi {{ $item->calculator?->icon ?? 'bi-calculator' }}"></i></span>
                        <div class="min-w-0">
                            <a href="{{ route('account.saved.show', $item) }}" class="fw-semibold text-decoration-none d-block">{{ $item->title }}</a>
                            <div class="small text-muted-custom">{{ $item->calculator?->title }} · {{ $item->created_at?->format('M j, Y') }}</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('account.saved.show', $item) }}" class="btn btn-sm btn-brand">View</a>
                        @if ($item->calculator)
                            <a href="{{ route('calculators.show', $item->calculator) }}" class="btn btn-sm btn-soft">Recalculate</a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card-surface p-4 text-muted-custom">
                    Nothing saved yet. After you calculate, click <strong>Save result</strong> on the calculator page.
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-3">{{ $saved->links() }}</div>
@endsection
