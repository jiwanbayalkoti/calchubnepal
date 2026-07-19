<x-guest-layout
    title="Choose a new password"
    subtitle="Pick a strong password you haven’t used here before."
>
    <form method="POST" action="{{ route('password.store') }}" class="auth-form" novalidate>
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <div class="auth-input-wrap">
                <i class="bi bi-envelope" aria-hidden="true"></i>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email', $request->email) }}"
                    class="form-control @error('email') is-invalid @enderror"
                    required
                    autofocus
                    autocomplete="username"
                >
            </div>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="auth-input-wrap">
                <i class="bi bi-lock" aria-hidden="true"></i>
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    required
                    autocomplete="new-password"
                    placeholder="New password"
                >
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Confirm password</label>
            <div class="auth-input-wrap">
                <i class="bi bi-shield-lock" aria-hidden="true"></i>
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    class="form-control"
                    required
                    autocomplete="new-password"
                    placeholder="Repeat new password"
                >
            </div>
        </div>

        <button type="submit" class="btn btn-brand w-100 auth-submit">
            Reset password
        </button>
    </form>
</x-guest-layout>
