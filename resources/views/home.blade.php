@extends('layouts.public')

@section('content')

    {{-- ============================================================ --}}
    {{-- Hero — brand + Try a Quick One widget                         --}}
    {{-- ============================================================ --}}
    <section class="hero-section atmosphere">
        <div class="atmosphere-shape" style="width:420px;height:420px;background:var(--brand);top:-140px;left:-120px;"></div>
        <div class="atmosphere-shape" style="width:340px;height:340px;background:var(--accent);top:40px;right:-100px;"></div>

        <div class="container position-relative" style="z-index:2;">
            <div class="row align-items-center g-4 g-xl-5">
                <div class="col-lg-5 col-xl-6 text-center text-lg-start hero-copy">
                    <span class="hero-eyebrow"><i class="bi bi-stars"></i> {{ __('home.eyebrow') }}</span>
                    <h1 class="hero-title">{{ __('home.hero_title') }}</h1>
                    <p class="hero-subtitle hero-subtitle--left">{{ __('home.hero_subtitle') }}</p>
                    <a href="{{ route('calculators.index') }}" class="btn btn-brand btn-lg">
                        {{ __('home.cta_explore') }} <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="col-lg-7 col-xl-6">
                    @include('partials.home.quick-try')
                </div>
            </div>
        </div>
    </section>

    @include('partials.ads.header')

    @include('partials.home.how-to-use')

    {{-- ============================================================ --}}
    {{-- Popular calculators                                           --}}
    {{-- ============================================================ --}}
    <section class="section pt-0">
        <div class="container">
            <div class="section-heading d-flex flex-wrap justify-content-between align-items-end">
                <div>
                    <span class="eyebrow">{{ __('home.trending') }}</span>
                    <h2>{{ __('home.popular') }}</h2>
                </div>
                <a href="{{ route('calculators.index') }}" class="btn btn-outline-brand mt-2">{{ __('home.view_all') }}</a>
            </div>

            <div class="row g-3">
                @foreach($popularCalculators as $calculator)
                    <div class="col-6 col-md-4 col-lg-3">
                        <a href="{{ route('calculators.show', $calculator) }}" class="calc-card">
                            <span class="calc-icon"><i class="bi {{ $calculator->icon ?? 'bi-calculator' }}"></i></span>
                            <p class="calc-title">{{ $calculator->title }}</p>
                            <p class="calc-desc">{{ $calculator->short_description }}</p>
                            @if($calculator->is_premium)
                                <span class="calc-badge badge-soft-accent">{{ __('badge.premium') }}</span>
                            @else
                                <span class="calc-badge badge-soft-brand">{{ __('badge.free') }}</span>
                            @endif
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- Categories                                                    --}}
    {{-- ============================================================ --}}
    <section class="section pt-0">
        <div class="container">
            <div class="section-heading">
                <span class="eyebrow">{{ __('home.browse') }}</span>
                <h2>{{ __('home.by_category') }}</h2>
            </div>

            <div class="row g-3">
                @foreach($categories as $category)
                    <div class="col-md-6 col-lg-3">
                        <a href="{{ route('categories.show', $category) }}" class="category-card">
                            <span class="cat-icon"><i class="bi {{ $category->icon ?? 'bi-grid' }}"></i></span>
                            <span>
                                <span class="d-block fw-semibold">{{ $category->name }}</span>
                                <span class="text-muted-custom small">{{ __('home.calculators_count', ['count' => $category->calculators_count]) }}</span>
                            </span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @if($latestPosts->isNotEmpty())
        <section class="section pt-0">
            <div class="container">
                <div class="section-heading d-flex flex-wrap justify-content-between align-items-end">
                    <div>
                        <span class="eyebrow">{{ __('home.from_blog') }}</span>
                        <h2>{{ __('home.latest_guides') }}</h2>
                    </div>
                    <a href="{{ route('blog.index') }}" class="btn btn-outline-brand mt-2">{{ __('home.visit_blog') }}</a>
                </div>

                <div class="row g-4">
                    @foreach($latestPosts as $post)
                        <div class="col-md-4">
                            <a href="{{ route('blog.show', $post) }}" class="card-surface blog-card d-block h-100 text-decoration-none overflow-hidden">
                                @if($post->featured_image)
                                    <img src="{{ asset('storage/'.$post->featured_image) }}" class="w-100" style="height:180px;object-fit:cover;" alt="{{ $post->title }}">
                                @endif
                                <div class="p-3">
                                    <span class="badge-soft-brand mb-2 d-inline-block">{{ $post->category?->name ?? __('home.general') }}</span>
                                    <h3 class="h6 mb-2 text-ink" style="color: var(--ink);">{{ $post->title }}</h3>
                                    <p class="reading-meta mb-0"><i class="bi bi-clock"></i> {{ __('home.min_read', ['min' => $post->reading_time_minutes]) }}</p>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@endsection

@push('scripts')
<script>
(function ($) {
  $(document).on('click', '.js-focus-home-search', function (e) {
    const $input = $('.js-live-search').first();
    if (!$input.length) return;
    e.preventDefault();
    $('html, body').animate({ scrollTop: 0 }, 220);
    setTimeout(function () {
      $input.trigger('focus');
    }, 240);
  });
})(jQuery);
</script>
@endpush
