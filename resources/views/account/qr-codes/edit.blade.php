@extends('layouts.account')

@section('account')
    <div class="mb-4">
        <a href="{{ route('account.qr-codes.show', $qr) }}" class="small text-muted-custom text-decoration-none">&larr; Back to analytics</a>
        <h1 class="h3 mb-1 mt-1">Edit Dynamic QR</h1>
        <p class="text-muted-custom mb-0">Change destination, password or expiry — the short URL & printed QR stay the same.</p>
    </div>

    <div class="card-surface p-4">
        <form method="POST" action="{{ route('account.qr-codes.update', $qr) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label" for="title">Title</label>
                <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $qr->title) }}">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="destination_url">Destination URL</label>
                <input type="url" name="destination_url" id="destination_url" class="form-control @error('destination_url') is-invalid @enderror" value="{{ old('destination_url', $qr->destination_url) }}" required>
                @error('destination_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Short URL remains <code>{{ $qr->shortUrl() }}</code></div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label" for="password">New password (optional)</label>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="remove_password" value="1" id="remove_password">
                        <label class="form-check-label" for="remove_password">Remove password protection</label>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="expires_at">Expiry date</label>
                <input type="datetime-local" name="expires_at" id="expires_at" class="form-control @error('expires_at') is-invalid @enderror"
                       value="{{ old('expires_at', $qr->expires_at?->format('Y-m-d\\TH:i')) }}">
                @error('expires_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Leave empty for no expiry.</div>
            </div>

            <div class="mb-4">
                <label class="form-label" for="status">Status</label>
                <select name="status" id="status" class="form-select">
                    @foreach(\App\Enums\Qr\QrStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(old('status', $qr->status?->value) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-brand">Save changes</button>
                <a href="{{ route('account.qr-codes.show', $qr) }}" class="btn btn-outline-brand">Cancel</a>
            </div>
        </form>

        <hr class="my-4">

        <form method="POST" action="{{ route('account.qr-codes.destroy', $qr) }}" onsubmit="return confirm('Delete this dynamic QR and all scan analytics?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm">Delete QR permanently</button>
        </form>
    </div>
@endsection
