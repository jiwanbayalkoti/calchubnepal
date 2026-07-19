@php
    $meta = $meta ?? [];
    $metaTitle = $meta['title'] ?? config('app.name');
    $metaDescription = $meta['description'] ?? 'Free, accurate, AI-powered calculators for finance, health, construction, math and everyday life.';
    $metaKeywords = $meta['keywords'] ?? null;
    $metaCanonical = $meta['canonical'] ?? url()->current();
    $metaImage = $meta['og_image'] ?? asset('images/og-default.png');
    $metaRobots = $meta['robots'] ?? 'index,follow';
@endphp

<title>{{ $metaTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">
@if($metaKeywords)
    <meta name="keywords" content="{{ $metaKeywords }}">
@endif
<meta name="robots" content="{{ $metaRobots }}">
<link rel="canonical" href="{{ $metaCanonical }}">

{{-- Open Graph --}}
<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:url" content="{{ $metaCanonical }}">
<meta property="og:image" content="{{ $metaImage }}">
<meta property="og:locale" content="{{ app()->getLocale() }}">

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
<meta name="twitter:image" content="{{ $metaImage }}">
