<x-guest-layout
    title="Create your account"
    subtitle="Join AI Calculator Hub to save work, get AI explanations, and access premium calculators."
>
    <form method="POST" action="{{ route('register') }}" class="auth-form" novalidate>
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <div class="auth-input-wrap">
                <i class="bi bi-person" aria-hidden="true"></i>
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    class="form-control @error('name') is-invalid @enderror"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Your full name"
                >
            </div>
            @error('name')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <div class="auth-input-wrap">
                <i class="bi bi-envelope" aria-hidden="true"></i>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="form-control @error('email') is-invalid @enderror"
                    required
                    autocomplete="username"
                    placeholder="you@example.com"
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
                    placeholder="Create a password"
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
                    placeholder="Repeat your password"
                >
            </div>
        </div>

        <button type="submit" class="btn btn-brand w-100 auth-submit">
            Create account
        </button>
    </form>

    @include('partials.auth.google-button')

    <p class="auth-switch">
        Already registered?
        <a href="{{ route('login', ['page' => 1]) }}" class="auth-link">Log in</a>
    </p>
</x-guest-layout>
