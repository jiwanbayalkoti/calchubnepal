@extends('layouts.account')

@section('account')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Calculation history</h1>
            <p class="text-muted-custom mb-0">Results from calculators you used while logged in.</p>
        </div>
        @if ($histories->total() > 0)
            <form method="POST" action="{{ route('account.history.clear') }}" onsubmit="return confirm('Clear all history?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">Clear all</button>
            </form>
        @endif
    </div>

    <div class="card-surface p-0 overflow-hidden">
        @forelse ($histories as $item)
            <div class="account-list-item px-3 px-md-4">
                <div class="d-flex align-items-center gap-3">
                    <span class="calc-icon" style="width:40px;height:40px;"><i class="bi {{ $item->calculator?->icon ?? 'bi-calculator' }}"></i></span>
                    <div class="min-w-0 flex-grow-1">
                        <a href="{{ $item->calculator ? route('calculators.show', $item->calculator) : '#' }}" class="fw-semibold text-decoration-none">
                            {{ $item->calculator?->title ?? 'Calculator' }}
                        </a>
                        <div class="small text-muted-custom">{{ $item->created_at?->format('M j, Y g:i A') }}</div>
                    </div>
                    <form method="POST" action="{{ route('account.history.destroy', $item) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-soft" title="Remove"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-4 text-muted-custom">No history yet. Run a calculator while logged in to see it here.</div>
        @endforelse
    </div>

    <div class="mt-3">{{ $histories->links() }}</div>
@endsection
