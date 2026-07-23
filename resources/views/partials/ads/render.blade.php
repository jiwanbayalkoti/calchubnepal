{{--
    Shared ad renderer with slideshow when multiple ads exist for a position.
    Pass: $position
    Master switch: Admin Settings → site.enable_ads
--}}
@php
    $position = $position ?? 'sidebar';
    $adsMasterOn = $hub->adsEnabled();

    $variants = [
        'header' => 'leaderboard',
        'sidebar' => 'box',
        'sticky' => 'sticky',
        'footer' => 'leaderboard',
        'in_content' => 'inline',
        'between_results' => 'inline',
    ];
    $variant = $variants[$position] ?? 'box';

    $ads = [];
    if ($adsMasterOn) {
        $ads = \Illuminate\Support\Facades\Cache::remember(
            "calc_hub:ads:{$position}",
            300,
            function () use ($position) {
                return \App\Models\Advertisement::query()
                    ->active()
                    ->forPosition($position)
                    ->orderByRaw("FIELD(ad_type, 'banner', 'html', 'affiliate', 'adsense')")
                    ->orderBy('sort_order')
                    ->get()
                    ->filter(function ($item) {
                        if (! $item->isCurrentlyRunning()) {
                            return false;
                        }

                        return filled($item->content)
                            || filled($item->image)
                            || filled($item->adsense_code);
                    })
                    ->values()
                    ->map(fn ($row) => [
                        'id' => $row->id,
                        'name' => $row->name,
                        'ad_type' => $row->ad_type,
                        'adsense_code' => $row->adsense_code,
                        'image' => $row->image,
                        'link_url' => $row->link_url,
                        'content' => $row->content,
                        'impression_url' => route('ads.impression', $row->id),
                        'click_url' => route('ads.click', $row->id),
                    ])
                    ->all();
            }
        );
    }

    $count = is_array($ads) ? count($ads) : 0;
    $carouselId = 'adCarousel-'.preg_replace('/[^a-z0-9]+/i', '-', $position).'-'.substr(md5($position.($count)), 0, 6);

    $wrapperClass = match ($position) {
        'header', 'footer' => 'ad-slot ad-header my-3',
        'sticky' => 'ad-slot ad-sticky',
        'in_content', 'between_results' => 'ad-slot ad-inline my-4',
        default => 'ad-slot ad-sidebar mb-3',
    };
    $hasAdsenseFallback = $adsMasterOn
        && $count === 0
        && $hub->adsenseEnabled()
        && filled($hub->adsenseSlot($position));
@endphp

@if ($adsMasterOn)
@if ($count === 0 && ! $hasAdsenseFallback && ! config('calculator_hub.ads_demo_mode'))
    {{-- Production / AdSense review: do not render empty promotional placeholders --}}
@else
<div class="{{ $wrapperClass }} ad-slot-{{ $variant }}" role="complementary" aria-label="Advertisement">
    @if ($count === 0 && $hasAdsenseFallback)
        @include('partials.ads.adsense-unit', ['position' => $position])
    @elseif ($count === 0)
        @include('partials.ads.demo', ['position' => $position, 'variant' => $variant])
    @elseif ($count === 1)
        @include('partials.ads.slide', ['ad' => $ads[0], 'variant' => $variant, 'themeIndex' => 0])
    @else
        <div id="{{ $carouselId }}" class="carousel slide ad-carousel" data-bs-ride="carousel" data-bs-interval="4500">
            <div class="carousel-indicators ad-carousel-indicators">
                @foreach ($ads as $i => $ad)
                    <button type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide-to="{{ $i }}"
                            class="{{ $i === 0 ? 'active' : '' }}" aria-current="{{ $i === 0 ? 'true' : 'false' }}"
                            aria-label="Slide {{ $i + 1 }}"></button>
                @endforeach
            </div>
            <div class="carousel-inner">
                @foreach ($ads as $i => $ad)
                    <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                        @include('partials.ads.slide', ['ad' => $ad, 'variant' => $variant, 'themeIndex' => $i])
                    </div>
                @endforeach
            </div>
            <button class="carousel-control-prev ad-carousel-control" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next ad-carousel-control" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    @endif
</div>
@endif
@endif
