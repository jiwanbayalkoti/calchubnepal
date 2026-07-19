<x-guest-layout
    title="Reset your password"
    subtitle="Enter your email and we’ll send a link to choose a new password."
>
    @if (session('status'))
        <div class="alert alert-success auth-alert" role="status">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="auth-form" novalidate>
        @csrf

        <div class="mb-4">
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
                    placeholder="you@example.com"
                >
            </div>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-brand w-100 auth-submit">
            Email reset link
        </button>
    </form>

    <p class="auth-switch">
        <a href="{{ route('home', ['auth' => 'login']) }}" class="auth-link">
            <i class="bi bi-arrow-left"></i> Back to login
        </a>
    </p>
</x-guest-layout>
