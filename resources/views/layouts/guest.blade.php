<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0B6E4F">

    <title>{{ $title ? $title.' | ' : '' }}{{ $hub->siteName() }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&family=Fraunces:opsz,wght@9..144,400..700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/calculator-hub.css') }}">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon-32x32.png') }}" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}" sizes="180x180">
</head>
<body class="auth-body">

<div class="auth-shell">
    <div class="auth-atmosphere" aria-hidden="true"></div>

    <header class="auth-topbar">
        <a class="brand-logo" href="{{ route('home') }}">
            <span class="brand-mark"><i class="bi bi-calculator"></i></span>
            <span>{{ $hub->siteName() }}</span>
        </a>

        <div class="d-flex align-items-center gap-2">
            <button type="button" class="theme-toggle" aria-label="Toggle dark mode">
                <i class="bi bi-moon-stars"></i>
            </button>
            <a href="{{ route('home') }}" class="btn btn-sm btn-soft d-none d-sm-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i> Home
            </a>
        </div>
    </header>

    <main class="auth-main">
        <div class="auth-panel auth-panel-enter">
            <div class="auth-panel-brand">
                <span class="brand-mark brand-mark-lg"><i class="bi bi-calculator"></i></span>
                <h1 class="auth-panel-title">{{ $title ?? 'Welcome' }}</h1>
                @if ($subtitle)
                    <p class="auth-panel-subtitle">{{ $subtitle }}</p>
                @endif
            </div>

            {{ $slot }}
        </div>
    </main>

    <footer class="auth-footer">
        <span>&copy; {{ date('Y') }} {{ $hub->siteName() }}</span>
        <span class="auth-footer-links">
            <a href="{{ route('privacy') }}">Privacy</a>
            <a href="{{ route('terms') }}">Terms</a>
            <a href="{{ route('cookies') }}">Cookies</a>
            <a href="{{ route('disclaimer') }}">Disclaimer</a>
        </span>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/calculator-hub.js') }}"></script>
</body>
</html>
