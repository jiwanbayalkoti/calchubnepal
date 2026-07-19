<x-guest-layout
    title="Welcome back"
    subtitle="Sign in to save calculations, export reports, and unlock premium tools."
>
    @if (session('status'))
        <div class="alert alert-success auth-alert" role="status">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="auth-form" novalidate>
        @csrf

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
                    autofocus
                    autocomplete="username"
                    placeholder="you@example.com"
                >
            </div>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <label for="password" class="form-label mb-0">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="auth-link small">Forgot password?</a>
                @endif
            </div>
            <div class="auth-input-wrap mt-2">
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

        <div class="form-check mb-4">
            <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
            <label for="remember_me" class="form-check-label">Remember me</label>
        </div>

        <button type="submit" class="btn btn-brand w-100 auth-submit">
            Log in
        </button>
    </form>

    @include('partials.auth.google-button')

    <p class="auth-switch">
        New here?
        <a href="{{ route('register', ['page' => 1]) }}" class="auth-link">Create an account</a>
    </p>
</x-guest-layout>
