<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- theme-color also set in seo-meta from admin settings --}}

    @include('partials.seo-meta')
    @unless(app(\App\Services\Seo\SeoService::class)->shouldNoindexRequest())
        @include('partials.seo-global-schemas')
    @endunless

    {{-- Early connections for critical third-party origins --}}
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://www.googletagmanager.com">
    <link rel="dns-prefetch" href="https://pagead2.googlesyndication.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>

    {{-- Critical above-the-fold CSS (inline) — improves FCP/LCP --}}
    @include('partials.critical-css')

    {{-- Fonts: fewer weights + non-blocking load (display=swap already set) --}}
    @php
        $fontCss = 'https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,600;700&display=swap';
        $bootstrapCss = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css';
        $iconsCss = 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css';
        $appCssFile = is_file(public_path('css/calculator-hub.min.css'))
            ? 'css/calculator-hub.min.css'
            : 'css/calculator-hub.css';
        $appJsFile = is_file(public_path('js/calculator-hub.min.js'))
            ? 'js/calculator-hub.min.js'
            : 'js/calculator-hub.js';
        $appCss = asset($appCssFile).'?v='.(@filemtime(public_path($appCssFile)) ?: '1');
        $appJs = asset($appJsFile).'?v='.(@filemtime(public_path($appJsFile)) ?: '1');
        $hasFlash = session('error') || session('status') || session('success');
        $loadSelect2 = request()->routeIs('calculators.*');
        $loadToastr = $hasFlash
            || request()->routeIs('calculators.*', 'contact', 'account.*', 'qr-code-generator*', 'visiting-card-designer*', 'search.*')
            || auth()->check();
        $loadSwal = request()->routeIs('calculators.*', 'contact') || auth()->check();
    @endphp

    <link rel="preload" as="style" href="{{ $fontCss }}">
    @include('partials.async-css', ['href' => $fontCss])

    {{-- Bootstrap grid is required for layout stability (CLS) — keep blocking but preload --}}
    <link rel="preload" as="style" href="{{ $bootstrapCss }}">
    <link rel="stylesheet" href="{{ $bootstrapCss }}">

    {{-- Icons + app CSS: icons non-blocking; app CSS blocking for visual stability --}}
    @include('partials.async-css', ['href' => $iconsCss])
    <link rel="preload" as="style" href="{{ $appCss }}">
    <link rel="stylesheet" href="{{ $appCss }}">

    @if ($loadSelect2)
        @include('partials.async-css', ['href' => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css'])
    @endif
    @if ($loadToastr)
        @include('partials.async-css', ['href' => 'https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css'])
    @endif

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon-32x32.png') }}" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}" sizes="180x180">

    {{-- Analytics delayed until idle (after interactive) --}}
    @include('partials.gtag')
    @include('partials.adsense')

    @stack('schemas')
    @stack('styles')
</head>
<body>

@if ($hasFlash)
    <div class="visually-hidden" id="flashMessages"
         data-error="{{ session('error') }}"
         data-status="{{ session('status') }}"
         data-success="{{ session('success') }}"></div>
@endif

<a href="#main-content" class="visually-hidden-focusable">{{ __('nav.skip') }}</a>

<header class="site-header">
    <nav class="navbar navbar-expand-lg py-2">
        <div class="container align-items-center">
            <a class="brand-logo" href="{{ route('home') }}">
                @if ($hub->hasLogo())
                    <img src="{{ $hub->logoUrl() }}" alt="{{ $hub->siteName() }}" style="max-height:40px;width:auto;">
                @else
                    <span class="brand-mark"><i class="bi bi-calculator"></i></span>
                    <span>{{ $hub->siteName() }}</span>
                @endif
            </a>

            <button class="navbar-toggler border-0 ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="{{ __('nav.menu') }}">
                <i class="bi bi-list fs-2"></i>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav main-nav ms-lg-4 me-lg-auto gap-lg-1">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">{{ __('nav.home') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('calculators.*') ? 'active' : '' }}" href="{{ route('calculators.index') }}">{{ __('nav.calculators') }}</a>
                    </li>
                    <li class="nav-item dropdown js-nav-dropdown">
                        @php
                            $toolsActive = request()->routeIs('qr-code-generator*', 'visiting-card-designer*', 'categories.*');
                            $navQrTypes = \App\Enums\Qr\QrType::cases();
                            $navCardStyles = [
                                'Professional' => 'bi-briefcase',
                                'Minimal' => 'bi-square',
                                'Bold' => 'bi-type-bold',
                                'Premium' => 'bi-gem',
                                'Creative' => 'bi-brush',
                                'Corporate' => 'bi-buildings',
                            ];
                            $navCardsByStyle = [];
                            foreach (\App\Enums\VisitingCard\CardTemplate::cases() as $cardTemplate) {
                                $navCardsByStyle[$cardTemplate->category()][] = $cardTemplate;
                            }
                            $navCategories = \Illuminate\Support\Facades\Cache::remember('calc_hub:nav:categories:icons', 3600, function () {
                                return \App\Models\CalculatorCategory::query()
                                    ->active()
                                    ->ordered()
                                    ->get(['name', 'slug', 'icon'])
                                    ->map(fn ($category) => [
                                        'name' => $category->name,
                                        'slug' => $category->slug,
                                        'icon' => $category->icon ?: 'bi-grid',
                                    ])
                                    ->all();
                            });
                        @endphp
                        <a class="nav-link dropdown-toggle {{ $toolsActive ? 'active' : '' }}" href="#" id="navToolsDropdown" role="button" aria-expanded="false" aria-haspopup="true">
                            {{ __('nav.tools') }}
                        </a>
                        <ul class="dropdown-menu nav-dropdown" aria-labelledby="navToolsDropdown">
                            <li class="nav-flyout js-nav-flyout">
                                <a class="dropdown-item has-flyout {{ request()->routeIs('qr-code-generator*') ? 'active' : '' }}" href="{{ route('qr-code-generator') }}">
                                    <i class="bi bi-qr-code"></i>
                                    <span>
                                        <strong>{{ __('nav.qr') }}</strong>
                                        <small>{{ __('nav.qr_hint') }}</small>
                                    </span>
                                    <i class="bi bi-chevron-right flyout-caret"></i>
                                </a>
                                <div class="nav-flyout-menu" aria-label="{{ __('nav.qr') }}">
                                    <div class="nav-flyout-grid">
                                        @foreach ($navQrTypes as $qrType)
                                            <a class="nav-flyout-tile" href="{{ route('qr-code-generator', ['type' => $qrType->value]) }}">
                                                <i class="bi {{ $qrType->icon() }}"></i>
                                                <span>{{ $qrType->label() }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                    <a class="nav-flyout-footer" href="{{ route('qr-code-generator') }}">
                                        {{ __('nav.view_all_qr') }} <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </li>
                            <li class="nav-flyout js-nav-flyout">
                                <a class="dropdown-item has-flyout {{ request()->routeIs('visiting-card-designer*') ? 'active' : '' }}" href="{{ route('visiting-card-designer') }}">
                                    <i class="bi bi-person-vcard"></i>
                                    <span>
                                        <strong>{{ __('nav.visiting_card') }}</strong>
                                        <small>{{ __('nav.visiting_card_hint') }}</small>
                                    </span>
                                    <i class="bi bi-chevron-right flyout-caret"></i>
                                </a>
                                <div class="nav-flyout-menu nav-flyout-menu--stack" aria-label="{{ __('nav.visiting_card') }}">
                                    <div class="nav-flyout-stack">
                                        @foreach ($navCardStyles as $style => $styleIcon)
                                            <div class="nav-flyout-nested js-nav-flyout-nested">
                                                <a class="nav-flyout-tile has-nested" href="{{ route('visiting-card-designer', ['style' => $style]) }}">
                                                    <i class="bi {{ $styleIcon }}"></i>
                                                    <span>{{ __('vc.filter_' . strtolower($style)) }}</span>
                                                    <i class="bi bi-chevron-right nested-caret"></i>
                                                </a>
                                                <div class="nav-flyout-submenu">
                                                    <div class="nav-flyout-grid nav-flyout-grid--templates">
                                                        @foreach ($navCardsByStyle[$style] ?? [] as $cardTemplate)
                                                            <a class="nav-flyout-tile" href="{{ route('visiting-card-designer', ['template' => $cardTemplate->value]) }}">
                                                                <i class="bi bi-person-vcard"></i>
                                                                <span>{{ $cardTemplate->label() }}</span>
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                    <a class="nav-flyout-footer" href="{{ route('visiting-card-designer', ['style' => $style]) }}">
                                                        {{ __('nav.view_style_cards') }} <i class="bi bi-arrow-right"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <a class="nav-flyout-footer" href="{{ route('visiting-card-designer') }}">
                                        {{ __('nav.view_all_cards') }} <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li class="nav-flyout js-nav-flyout">
                                <a class="dropdown-item has-flyout {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                                    <i class="bi bi-grid-3x3-gap"></i>
                                    <span>
                                        <strong>{{ __('nav.categories') }}</strong>
                                        <small>{{ __('nav.categories_hint') }}</small>
                                    </span>
                                    <i class="bi bi-chevron-right flyout-caret"></i>
                                </a>
                                <div class="nav-flyout-menu" aria-label="{{ __('nav.categories') }}">
                                    <div class="nav-flyout-grid">
                                        @foreach ($navCategories as $navCategory)
                                            <a class="nav-flyout-tile {{ request()->is('category/'.$navCategory['slug']) ? 'active' : '' }}" href="{{ route('categories.show', $navCategory['slug']) }}">
                                                <i class="bi {{ $navCategory['icon'] }}"></i>
                                                <span>{{ $navCategory['name'] }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                    <a class="nav-flyout-footer" href="{{ route('categories.index') }}">
                                        {{ __('nav.view_all_categories') }} <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('blog.*') ? 'active' : '' }}" href="{{ route('blog.index') }}">{{ __('nav.blog') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('pricing') ? 'active' : '' }}" href="{{ route('pricing') }}">{{ __('nav.pricing') }}</a>
                    </li>
                    <li class="nav-item dropdown js-nav-dropdown">
                        @php
                            $companyActive = request()->routeIs('about', 'contact');
                        @endphp
                        <a class="nav-link dropdown-toggle {{ $companyActive ? 'active' : '' }}" href="#" id="navCompanyDropdown" role="button" aria-expanded="false" aria-haspopup="true">
                            {{ __('nav.company') }}
                        </a>
                        <ul class="dropdown-menu nav-dropdown" aria-labelledby="navCompanyDropdown">
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">
                                    <i class="bi bi-info-circle"></i>
                                    <span><strong>{{ __('nav.about') }}</strong></span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">
                                    <i class="bi bi-envelope"></i>
                                    <span><strong>{{ __('nav.contact') }}</strong></span>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>

                <div class="header-actions d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center gap-2 ms-lg-3 mt-3 mt-lg-0">
                    <form class="search-box header-search" action="{{ route('search.results') }}" method="GET" role="search">
                        <i class="bi bi-search"></i>
                        <input type="search" name="q" value="{{ request('q') }}" class="form-control js-live-search" placeholder="{{ __('nav.search_placeholder') }}" autocomplete="off">
                    </form>

                    <div class="d-flex align-items-center gap-2 flex-wrap">
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
                                    <span class="d-none d-xl-inline">{{ \Illuminate\Support\Str::limit(auth()->user()->name, 14) }}</span>
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
                    @if ($hub->hasLogo())
                        <img src="{{ $hub->logoUrl() }}" alt="{{ $hub->siteName() }}" style="max-height:48px;width:auto;">
                    @else
                        <span class="brand-mark"><i class="bi bi-calculator"></i></span>
                        <span>{{ $hub->siteName() }}</span>
                    @endif
                </a>
                <p class="text-white-50">{{ __('footer.tagline') }}</p>
                {{-- Social icons hidden until profiles are created; uncomment and set links in Admin → Settings → Social.
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
                --}}
            </div>

            <div class="col-6 col-lg-2">
                <p class="footer-heading">{{ __('footer.company') }}</p>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ route('about') }}">{{ __('footer.about') }}</a></li>
                    <li class="mb-2"><a href="{{ route('qr-code-generator') }}">{{ __('footer.qr') }}</a></li>
                    <li class="mb-2"><a href="{{ route('visiting-card-designer') }}">{{ __('footer.visiting_card') }}</a></li>
                    <li class="mb-2"><a href="{{ route('pricing') }}">{{ __('footer.pricing') }}</a></li>
                    <li class="mb-2"><a href="{{ route('blog.index') }}">{{ __('footer.blog') }}</a></li>
                    <li class="mb-2"><a href="{{ route('contact') }}">{{ __('footer.contact') }}</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-3">
                <p class="footer-heading">{{ __('footer.categories') }}</p>
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
                <p class="footer-heading">{{ __('footer.legal') }}</p>
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

@auth
    @include('partials.breath-hold.certificate-modal')
@endauth

{{--
  Scripts at end of body (after content) so they do not block LCP paint.
  Not using defer on the core stack: @stack('scripts') contains inline jQuery
  that must run after these vendors in document order.
--}}
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@if ($loadSelect2)
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endif
@if ($loadToastr)
    <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
@endif
@if ($loadSwal)
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endif
<script src="{{ $appJs }}"></script>

@stack('scripts')
{!! $hub->footerScripts() !!}
</body>
</html>
