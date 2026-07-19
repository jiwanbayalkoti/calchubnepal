@extends('layouts.account')

@section('account')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
        <div>
            <a href="{{ route('account.saved.index') }}" class="small text-decoration-none">&larr; Back to saved</a>
            <h1 class="h3 mb-1 mt-2">{{ $saved->title }}</h1>
            <p class="text-muted-custom mb-0">
                {{ $saved->calculator?->title }} · {{ $saved->created_at?->format('M j, Y g:i A') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            @if ($saved->calculator)
                <a href="{{ route('calculators.show', $saved->calculator) }}" class="btn btn-sm btn-brand">Open calculator</a>
            @endif
            <form method="POST" action="{{ route('account.saved.destroy', $saved) }}" onsubmit="return confirm('Delete this saved calculation?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card-surface p-3 p-md-4 h-100">
                <h2 class="h6 text-uppercase text-muted-custom mb-3">Inputs</h2>
                <ul class="list-unstyled mb-0">
                    @foreach ($saved->inputs ?? [] as $key => $value)
                        <li class="mb-2">
                            <strong>{{ ucwords(str_replace('_', ' ', (string) $key)) }}:</strong>
                            @if (is_array($value))
                                <pre class="small mt-1 mb-0 p-2 rounded" style="background: rgba(var(--brand-rgb), .06);">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            @else
                                {{ $value }}
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card-surface p-3 p-md-4 h-100">
                <h2 class="h6 text-uppercase text-muted-custom mb-3">Outputs</h2>
                <ul class="list-unstyled mb-0">
                    @foreach ($saved->outputs ?? [] as $key => $value)
                        <li class="mb-2">
                            <strong>{{ ucwords(str_replace('_', ' ', (string) $key)) }}:</strong>
                            @if (is_array($value))
                                <pre class="small mt-1 mb-0 p-2 rounded" style="background: rgba(var(--brand-rgb), .06);">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            @else
                                {{ $value }}
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    @if ($saved->notes)
        <div class="card-surface p-3 p-md-4 mt-3">
            <h2 class="h6 text-uppercase text-muted-custom mb-2">Notes</h2>
            <p class="mb-0">{{ $saved->notes }}</p>
        </div>
    @endif
@endsection
