{{-- Visual demo ad for local / empty slots. Not real AdSense. --}}
@php
    $position = $position ?? 'sidebar';
    $variant = $variant ?? 'box'; // leaderboard | box | skyscraper | sticky | inline
    $labels = [
        'header' => 'Header · Leaderboard',
        'sidebar' => 'Sidebar · Rectangle',
        'sticky' => 'Sticky · Skyscraper',
        'footer' => 'Footer · Banner',
        'in_content' => 'In Content · Inline',
        'between_results' => 'Between Results · Inline',
    ];
    $label = $labels[$position] ?? \Illuminate\Support\Str::title(str_replace('_', ' ', $position));
@endphp

<div class="ad-demo ad-demo-{{ $variant }}" data-ad-position="{{ $position }}">
    <span class="ad-demo-badge">Ad demo</span>
    <div class="ad-demo-body">
        <strong>{{ $label }}</strong>
        <span>Sample advertisement placement</span>
        <small>Local preview only — replace with AdSense later</small>
    </div>
</div>
