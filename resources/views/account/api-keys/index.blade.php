@extends('layouts.account')

@section('account')
    <h1 class="h3 mb-1">API Keys</h1>
    <p class="text-muted-custom mb-4">Authenticate REST calls with <code>X-Api-Key</code> or Sanctum bearer. Limit: {{ $maxKeys }} keys.</p>

    @if($plainKey)
        <div class="alert alert-success">
            <strong>Copy your API key now</strong> (shown once):
            <code class="d-block mt-2 user-select-all">{{ $plainKey }}</code>
        </div>
    @endif

    <div class="card-surface p-3 p-md-4 mb-4">
        <form method="POST" action="{{ route('account.api-keys.store') }}" class="row g-3">
            @csrf
            <div class="col-md-6"><input name="name" class="form-control" placeholder="Key name (e.g. Production)" required></div>
            <div class="col-md-3"><input type="number" name="rate_limit_per_minute" class="form-control" min="10" max="600" value="60" placeholder="Rate limit"></div>
            <div class="col-md-3"><button class="btn btn-brand w-100">Create key</button></div>
        </form>
    </div>

    <div class="card-surface p-3 p-md-4">
        @forelse($keys as $key)
            <div class="account-list-item d-flex justify-content-between align-items-center gap-2">
                <div>
                    <strong>{{ $key->name }}</strong>
                    <div class="small text-muted-custom">{{ $key->key_prefix }}… · {{ $key->is_active ? 'Active' : 'Disabled' }} · last used {{ $key->last_used_at?->diffForHumans() ?: 'never' }}</div>
                </div>
                <div class="d-flex gap-1">
                    <form method="POST" action="{{ route('account.api-keys.toggle', $key) }}">@csrf<button class="btn btn-sm btn-soft">{{ $key->is_active ? 'Disable' : 'Enable' }}</button></form>
                    <form method="POST" action="{{ route('account.api-keys.destroy', $key) }}" onsubmit="return confirm('Revoke this key?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Revoke</button></form>
                </div>
            </div>
        @empty
            <p class="text-muted-custom mb-0">No API keys yet.</p>
        @endforelse
    </div>
@endsection
