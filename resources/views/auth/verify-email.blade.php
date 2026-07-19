<x-guest-layout
    title="Verify your email"
    subtitle="Thanks for signing up. Click the link we emailed you to get started."
>
    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success auth-alert" role="status">
            A new verification link has been sent to your email address.
        </div>
    @endif

    <p class="auth-panel-subtitle mb-4">
        Didn’t get the email? We can send another one.
    </p>

    <form method="POST" action="{{ route('verification.send') }}" class="auth-form mb-3">
        @csrf
        <button type="submit" class="btn btn-brand w-100 auth-submit">
            Resend verification email
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline-brand w-100">
            Log out
        </button>
    </form>
</x-guest-layout>
