{{-- Single designed banner slide. Expects $ad array + $variant + $themeIndex --}}
@php
    $themes = [
        ['grad' => 'ad-theme-forest', 'icon' => 'bi-lightning-charge-fill'],
        ['grad' => 'ad-theme-ocean', 'icon' => 'bi-graph-up-arrow'],
        ['grad' => 'ad-theme-sunset', 'icon' => 'bi-stars'],
        ['grad' => 'ad-theme-slate', 'icon' => 'bi-shield-check'],
    ];
    $theme = $themes[($themeIndex ?? 0) % count($themes)];
    $title = $ad['name'] ?? 'Sponsored';
    $text = $ad['content'] ?? '';
    $isHtml = is_string($text) && str_contains($text, '<');
    $clickUrl = $ad['click_url'] ?? ($ad['link_url'] ?? null);
    $impressionUrl = $ad['impression_url'] ?? null;
    $image = $ad['image'] ?? null;
    $imageUrl = null;
    $imageWidth = null;
    $imageHeight = null;
    if ($image) {
        if (str_starts_with($image, 'http')) {
            $imageUrl = $image;
        } else {
            $webpRel = preg_replace('/\.(png|jpe?g)$/i', '.webp', $image);
            if ($webpRel && $webpRel !== $image && is_file(storage_path('app/public/'.$webpRel))) {
                $imageUrl = asset('storage/'.$webpRel);
            } else {
                $imageUrl = asset('storage/'.$image);
            }
        }
    }
@endphp

@if ($impressionUrl)
    <img src="{{ $impressionUrl }}" alt="" width="1" height="1" style="position:absolute;width:1px;height:1px;opacity:0;pointer-events:none;" loading="lazy" decoding="async">
@endif

@if (! empty($ad['adsense_code']) && empty($ad['content']) && empty($image))
    <div class="ad-banner-slide">
        {!! $ad['adsense_code'] !!}
    </div>
@elseif ($imageUrl)
    <a href="{{ $clickUrl ?: '#' }}" class="ad-banner-slide ad-banner-image {{ $clickUrl ? '' : 'pe-none' }}"
       @if($clickUrl) target="_blank" rel="noopener sponsored" @endif>
        <img src="{{ $imageUrl }}" alt="{{ $title }}" class="ad-banner-img" loading="lazy" decoding="async" width="728" height="90">
        <span class="ad-banner-sponsored">Sponsored</span>
    </a>
@elseif ($isHtml)
    <div class="ad-banner-slide ad-banner-html">
        {!! $text !!}
    </div>
@else
    <a href="{{ $clickUrl ?: '#' }}"
       class="ad-banner-slide ad-banner-card {{ $theme['grad'] }} {{ $clickUrl ? '' : 'pe-none' }}"
       @if($clickUrl) target="_blank" rel="noopener sponsored" @endif>
        <span class="ad-banner-sponsored">Sponsored</span>
        <div class="ad-banner-icon"><i class="bi {{ $theme['icon'] }}"></i></div>
        <div class="ad-banner-copy">
            <strong class="ad-banner-title">{{ $title }}</strong>
            @if ($text)
                <p class="ad-banner-text">{{ $text }}</p>
            @endif
            @if ($clickUrl)
                <span class="ad-banner-cta">Learn more <i class="bi bi-arrow-right"></i></span>
            @endif
        </div>
    </a>
@endif
