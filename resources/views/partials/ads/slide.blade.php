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
    $link = $ad['link_url'] ?? null;
    $image = $ad['image'] ?? null;
    $imageUrl = $image
        ? (str_starts_with($image, 'http') ? $image : asset('storage/'.$image))
        : null;
@endphp

@if (! empty($ad['adsense_code']) && empty($ad['content']) && empty($image))
    <div class="ad-banner-slide">
        {!! $ad['adsense_code'] !!}
    </div>
@elseif ($imageUrl)
    <a href="{{ $link ?: '#' }}" class="ad-banner-slide ad-banner-image {{ $link ? '' : 'pe-none' }}"
       @if($link) target="_blank" rel="noopener sponsored" @endif>
        <img src="{{ $imageUrl }}" alt="{{ $title }}" class="ad-banner-img">
        <span class="ad-banner-sponsored">Sponsored</span>
    </a>
@elseif ($isHtml)
    <div class="ad-banner-slide ad-banner-html">
        {!! $text !!}
    </div>
@else
    <a href="{{ $link ?: '#' }}"
       class="ad-banner-slide ad-banner-card {{ $theme['grad'] }} {{ $link ? '' : 'pe-none' }}"
       @if($link) target="_blank" rel="noopener sponsored" @endif>
        <span class="ad-banner-sponsored">Sponsored</span>
        <div class="ad-banner-icon"><i class="bi {{ $theme['icon'] }}"></i></div>
        <div class="ad-banner-copy">
            <strong class="ad-banner-title">{{ $title }}</strong>
            @if ($text)
                <p class="ad-banner-text">{{ $text }}</p>
            @endif
            @if ($link)
                <span class="ad-banner-cta">Learn more <i class="bi bi-arrow-right"></i></span>
            @endif
        </div>
    </a>
@endif
