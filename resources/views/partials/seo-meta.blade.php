@php
    $meta = $meta ?? [];
    $hub = $hub ?? app(\App\Services\Settings\AppSettings::class);
    $seo = app(\App\Services\Seo\SeoService::class);
    $metaTitle = $meta['title'] ?? $hub->defaultMetaTitle();
    $metaDescription = $meta['description'] ?? $hub->defaultMetaDescription();
    $metaKeywords = $meta['keywords'] ?? $hub->defaultMetaKeywords();
    $metaCanonical = $seo->normalizeCanonical((string) ($meta['canonical'] ?? url()->current()));
    $rawImage = (string) ($meta['og_image'] ?? $hub->defaultOgImage() ?? asset('images/og-default.webp'));
    if ($rawImage !== '' && ! str_starts_with($rawImage, 'http') && ! str_starts_with($rawImage, '/')) {
        $rawImage = asset('storage/'.ltrim($rawImage, '/'));
    }
    $metaImage = preg_replace(
        '#^https?://[^/]+#i',
        rtrim((string) config('app.url'), '/'),
        $rawImage
    ) ?: $rawImage;
    $metaRobots = $meta['robots'] ?? 'index,follow';
    $ogType = $meta['og_type'] ?? 'website';
    $metaAuthor = $meta['author'] ?? $hub->publisherName();
    $metaPublisher = $meta['publisher'] ?? $hub->publisherName();
    $metaLanguage = $meta['language'] ?? str_replace('_', '-', app()->getLocale());
    $themeColor = $meta['theme_color'] ?? $hub->themeColor();
    $twitterSite = $meta['twitter_site'] ?? $hub->twitterHandle();
@endphp

<title>{{ $metaTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">
@if($metaKeywords)
    <meta name="keywords" content="{{ $metaKeywords }}">
@endif
<meta name="robots" content="{{ $metaRobots }}">
<meta name="author" content="{{ $metaAuthor }}">
<meta name="publisher" content="{{ $metaPublisher }}">
<meta name="language" content="{{ $metaLanguage }}">
<meta name="theme-color" content="{{ $themeColor }}">
<link rel="canonical" href="{{ $metaCanonical }}">

@if($verify = $hub->searchConsoleVerification())
    <meta name="google-site-verification" content="{{ $verify }}">
@endif
@if($bing = $hub->bingVerification())
    <meta name="msvalidate.01" content="{{ $bing }}">
@endif

{{-- Open Graph — per-page type (article for blogs, website otherwise) --}}
<meta property="og:site_name" content="{{ $hub->siteName() }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:url" content="{{ $metaCanonical }}">
<meta property="og:image" content="{{ $metaImage }}">
<meta property="og:locale" content="{{ app()->getLocale() }}">

{{-- Twitter Cards --}}
<meta name="twitter:card" content="summary_large_image">
@if($twitterSite)
    <meta name="twitter:site" content="{{ $twitterSite }}">
@endif
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
<meta name="twitter:image" content="{{ $metaImage }}">

{{-- Favicons / PWA hints (paths configurable via public assets) --}}
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
<link rel="icon" type="image/png" href="{{ asset('images/favicon-32x32.png') }}" sizes="32x32">
<link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
@if(file_exists(public_path('site.webmanifest')))
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
@endif
