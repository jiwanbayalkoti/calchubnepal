@extends('layouts.account')

@section('account')
    <h1 class="h3 mb-1">Profile settings</h1>
    <p class="text-muted-custom mb-4">Update your personal details and password.</p>

    <div class="card-surface p-3 p-md-4 mb-4">
        <h2 class="h5 mb-3">Account details</h2>
        <form method="POST" action="{{ route('account.profile.update') }}" novalidate>
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="form-control @error('name') is-invalid @enderror" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="form-control @error('email') is-invalid @enderror" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="phone" class="form-label">Phone <span class="text-muted-custom">(optional)</span></label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                           class="form-control @error('phone') is-invalid @enderror">
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <button type="submit" class="btn btn-brand mt-3">Save changes</button>
        </form>
    </div>

    <div class="card-surface p-3 p-md-4 mb-4">
        <h2 class="h5 mb-3">Change password</h2>
        <form method="POST" action="{{ route('password.update') }}" novalidate>
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-4">
                    <label for="current_password" class="form-label">Current password</label>
                    <input id="current_password" type="password" name="current_password"
                           class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" required autocomplete="current-password">
                    @error('current_password', 'updatePassword') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label for="password" class="form-label">New password</label>
                    <input id="password" type="password" name="password"
                           class="form-control @error('password', 'updatePassword') is-invalid @enderror" required autocomplete="new-password">
                    @error('password', 'updatePassword') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label for="password_confirmation" class="form-label">Confirm password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation"
                           class="form-control" required autocomplete="new-password">
                </div>
            </div>

            <button type="submit" class="btn btn-outline-brand mt-3">Update password</button>
        </form>
    </div>

    <div class="card-surface p-3 p-md-4 border border-danger-subtle">
        <h2 class="h5 text-danger mb-2">Delete account</h2>
        <p class="text-muted-custom small mb-3">This permanently removes your account, history, favorites and saved calculations.</p>
        <form method="POST" action="{{ route('account.profile.destroy') }}" onsubmit="return confirm('Delete your account permanently? This cannot be undone.');">
            @csrf
            @method('DELETE')
            <div class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label for="delete_password" class="form-label">Confirm with password</label>
                    <input id="delete_password" type="password" name="password"
                           class="form-control @error('password', 'userDeletion') is-invalid @enderror" required>
                    @error('password', 'userDeletion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-outline-danger">Delete account</button>
                </div>
            </div>
        </form>
    </div>
@endsection
