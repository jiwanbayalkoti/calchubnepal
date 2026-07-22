<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0B6E4F">

    @include('partials.gtag')
    @include('partials.seo-meta')

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&family=Fraunces:opsz,wght@9..144,400..700&display=swap" rel="stylesheet">

    {{-- Bootstrap 5.3 + Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- Select2 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">

    {{-- Toastr --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">

    {{-- App design system (filemtime busts CDN/browser cache after CSS updates) --}}
    <link rel="stylesheet" href="{{ asset('css/calculator-hub.css') }}?v={{ @filemtime(public_path('css/calculator-hub.css')) ?: '1' }}">

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon-32x32.png') }}" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}" sizes="180x180">

    @include('partials.adsense')

    @stack('schemas')
    @stack('styles')
</head>
<body>

@if (session('error') || session('status') || session('success'))
    <div class="visually-hidden" id="flashMessages"
         data-error="{{ session('error') }}"
         data-status="{{ session('status') }}"
         data-success="{{ session('success') }}"></div>
@endif

<a href="#main-content" class="visually-hidden-focusable">{{ __('nav.skip') }}</a>

<header class="site-header">
    <nav class="navbar navbar-expand-lg py-2">
        <div class="container d-flex align-items-center gap-3">
            <a class="brand-logo" href="{{ route('home') }}">
                <span class="brand-mark"><i class="bi bi-calculator"></i></span>
                <span>{{ $hub->siteName() }}</span>
            </a>

            <button class="navbar-toggler border-0 ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="{{ __('nav.menu') }}">
                <i class="bi bi-list fs-2"></i>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav main-nav mx-lg-3 gap-1">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">{{ __('nav.home') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('calculators.*') ? 'active' : '' }}" href="{{ route('calculators.index') }}">{{ __('nav.calculators') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">{{ __('nav.categories') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('blog.*') ? 'active' : '' }}" href="{{ route('blog.index') }}">{{ __('nav.blog') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">{{ __('nav.about') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('pricing') ? 'active' : '' }}" href="{{ route('pricing') }}">{{ __('nav.pricing') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">{{ __('nav.contact') }}</a></li>
                </ul>

                <form class="search-box mx-lg-2 my-2 my-lg-0 flex-grow-1" style="max-width: 320px;" action="{{ route('search.results') }}" method="GET" role="search">
                    <i class="bi bi-search"></i>
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control js-live-search" placeholder="{{ __('nav.search_placeholder') }}" autocomplete="off">
                </form>

                <div class="d-flex align-items-center gap-2 ms-lg-2 mt-2 mt-lg-0">
                    <div class="lang-switch btn-group" role="group" aria-label="{{ __('nav.language') }}">
                        <a href="{{ route('locale.switch', 'en') }}" class="btn btn-sm {{ app()->getLocale() === 'en' ? 'btn-brand' : 'btn-outline-brand' }}" hreflang="en">EN</a>
                        <a href="{{ route('locale.switch', 'ne') }}" class="btn btn-sm {{ app()->getLocale() === 'ne' ? 'btn-brand' : 'btn-outline-brand' }}" hreflang="ne">NE</a>
                    </div>

                    <button type="button" class="theme-toggle" aria-label="{{ __('nav.theme') }}">
                        <i class="bi bi-moon-stars"></i>
                    </button>

                    @auth
                        <div class="dropdown">
                            <button class="btn btn-sm btn-soft dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i>
                                <span class="d-none d-lg-inline">{{ \Illuminate\Support\Str::limit(auth()->user()->name, 14) }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end account-dropdown">
                                <li><a class="dropdown-item" href="{{ route('account.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>{{ __('nav.dashboard') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('account.saved.index') }}"><i class="bi bi-bookmark-star me-2"></i>{{ __('nav.saved') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('account.favorites.index') }}"><i class="bi bi-heart me-2"></i>{{ __('nav.favorites') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('account.subscription') }}"><i class="bi bi-credit-card me-2"></i>{{ __('nav.subscription') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('account.profile.edit') }}"><i class="bi bi-person-gear me-2"></i>{{ __('nav.profile') }}</a></li>
                                @if (auth()->user()->canAccessAdmin())
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-shield-lock me-2"></i>{{ __('nav.admin') }}</a></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>{{ __('nav.logout') }}</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <button type="button" class="btn btn-sm btn-outline-brand d-none d-lg-inline-block js-open-auth" data-auth="login">{{ __('nav.login') }}</button>
                        <button type="button" class="btn btn-sm btn-brand js-open-auth" data-auth="register">{{ __('nav.signup') }}</button>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
</header>

@hasSection('breadcrumb')
    <div class="container pt-4">
        @yield('breadcrumb')
    </div>
@endif

@unless(request()->routeIs('home'))
    <div class="container">
        @include('partials.ads.header')
    </div>
@endunless

<main id="main-content">
    @yield('content')
</main>

<div class="container">
    @include('partials.ads.footer')
</div>

<footer class="site-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <a class="brand-logo mb-3 d-inline-flex" href="{{ route('home') }}">
                    <span class="brand-mark"><i class="bi bi-calculator"></i></span>
                    <span>{{ $hub->siteName() }}</span>
                </a>
                <p class="text-white-50">{{ __('footer.tagline') }}</p>
                <div class="social-icons mt-3">
                    @php
                        $socialIcons = [
                            'facebook' => 'bi-facebook',
                            'twitter' => 'bi-twitter-x',
                            'linkedin' => 'bi-linkedin',
                            'youtube' => 'bi-youtube',
                            'tiktok' => 'bi-tiktok',
                        ];
                    @endphp
                    @foreach ($hub->socialLinks() as $network => $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ ucfirst($network) }}"><i class="bi {{ $socialIcons[$network] ?? 'bi-link-45deg' }}"></i></a>
                    @endforeach
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <h6>{{ __('footer.company') }}</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ route('about') }}">{{ __('footer.about') }}</a></li>
                    <li class="mb-2"><a href="{{ route('pricing') }}">{{ __('footer.pricing') }}</a></li>
                    <li class="mb-2"><a href="{{ route('blog.index') }}">{{ __('footer.blog') }}</a></li>
                    <li class="mb-2"><a href="{{ route('contact') }}">{{ __('footer.contact') }}</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-3">
                <h6>{{ __('footer.categories') }}</h6>
                <ul class="list-unstyled">
                    @foreach(\Illuminate\Support\Facades\Cache::remember('calc_hub:footer:categories', 3600, function () {
                        return \App\Models\CalculatorCategory::query()
                            ->active()
                            ->ordered()
                            ->take(5)
                            ->get(['id', 'name', 'slug'])
                            ->map(fn ($category) => [
                                'name' => $category->name,
                                'slug' => $category->slug,
                            ])
                            ->all();
                    }) as $footerCategory)
                        <li class="mb-2"><a href="{{ route('categories.show', $footerCategory['slug']) }}">{{ $footerCategory['name'] }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="col-6 col-lg-3">
                <h6>{{ __('footer.legal') }}</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ route('privacy') }}">{{ __('footer.privacy') }}</a></li>
                    <li class="mb-2"><a href="{{ route('terms') }}">{{ __('footer.terms') }}</a></li>
                    <li class="mb-2"><a href="{{ route('cookies') }}">{{ __('footer.cookies') }}</a></li>
                    <li class="mb-2"><a href="{{ route('disclaimer') }}">{{ __('footer.disclaimer') }}</a></li>
                    <li class="mb-2"><a href="{{ route('sitemap') }}">{{ __('footer.sitemap') }}</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom d-flex flex-wrap justify-content-between gap-2">
            <span>&copy; {{ date('Y') }} {{ $hub->siteName() }}. {{ __('footer.rights') }}</span>
            <span>{!! __('footer.made', ['heart' => '<i class="bi bi-heart-fill text-accent"></i>']) !!}</span>
        </div>
    </div>
</footer>

@include('partials.ads.sticky')
@include('partials.cookie-consent')

@guest
    @include('partials.auth.modals')
@endguest

{{-- Core scripts --}}
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="{{ asset('js/calculator-hub.js') }}?v={{ @filemtime(public_path('js/calculator-hub.js')) ?: '1' }}"></script>

@stack('scripts')
</body>
</html>
