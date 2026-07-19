<x-guest-layout
    title="Confirm password"
    subtitle="This is a secure area. Please confirm your password to continue."
>
    <form method="POST" action="{{ route('password.confirm') }}" class="auth-form" novalidate>
        @csrf

        <div class="mb-4">
            <label for="password" class="form-label">Password</label>
            <div class="auth-input-wrap">
                <i class="bi bi-lock" aria-hidden="true"></i>
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    required
                    autocomplete="current-password"
                    placeholder="Your password"
                >
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-brand w-100 auth-submit">
            Confirm
        </button>
    </form>
</x-guest-layout>
