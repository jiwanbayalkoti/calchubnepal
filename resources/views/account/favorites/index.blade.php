@extends('layouts.account')

@section('account')
    <h1 class="h3 mb-1">Favorite calculators</h1>
    <p class="text-muted-custom mb-4">Quick access to tools you use often.</p>

    <div class="row g-3">
        @forelse ($favorites as $favorite)
            @php $calc = $favorite->calculator; @endphp
            @continue(! $calc)
            <div class="col-md-6">
                <div class="card-surface p-3 h-100 d-flex flex-column">
                    <div class="d-flex align-items-start gap-3 mb-2">
                        <span class="calc-icon" style="width:44px;height:44px;"><i class="bi {{ $calc->icon ?? 'bi-calculator' }}"></i></span>
                        <div class="min-w-0 flex-grow-1">
                            <a href="{{ route('calculators.show', $calc) }}" class="fw-semibold text-decoration-none d-block">{{ $calc->title }}</a>
                            <div class="small text-muted-custom">{{ $calc->category?->name }}</div>
                        </div>
                        @if ($calc->is_premium)
                            <span class="badge bg-warning text-dark">Premium</span>
                        @endif
                    </div>
                    <p class="small text-muted-custom flex-grow-1 mb-3">{{ \Illuminate\Support\Str::limit($calc->short_description, 100) }}</p>
                    <div class="d-flex gap-2">
                        <a href="{{ route('calculators.show', $calc) }}" class="btn btn-sm btn-brand">Open</a>
                        <form method="POST" action="{{ route('account.favorites.destroy', $favorite) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-soft">Remove</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card-surface p-4 text-muted-custom">
                    No favorites yet. Open any calculator and click the heart icon to save it here.
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-3">{{ $favorites->links() }}</div>
@endsection
