@extends('layouts.public')

@section('content')
    <section class="hero-section atmosphere">
        <div class="atmosphere-shape" style="width:420px;height:420px;background:var(--brand);top:-140px;left:-120px;"></div>
        <div class="atmosphere-shape" style="width:300px;height:300px;background:var(--accent);top:60px;right:-80px;"></div>

        <div class="container position-relative text-center" style="z-index:2;">
            <span class="hero-eyebrow"><i class="bi bi-info-circle"></i> {{ __('about.eyebrow') }}</span>
            <h1 class="hero-title mx-auto" style="font-size: clamp(2rem, 4vw, 3rem);">{{ __('about.hero_title') }}</h1>
            <p class="hero-subtitle mx-auto">{{ __('about.hero_subtitle') }}</p>
        </div>
    </section>

    <section class="section pt-0">
        <div class="container">
            <div class="row g-3 g-lg-4 mb-5 about-stats">
                <div class="col-md-4">
                    <div class="about-stat card-surface p-4 text-center h-100">
                        <div class="about-stat__value" data-count="{{ (int) $stats['calculators'] }}">{{ number_format($stats['calculators']) }}</div>
                        <div class="about-stat__label">{{ __('about.stat_calculators') }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="about-stat card-surface p-4 text-center h-100">
                        <div class="about-stat__value" data-count="{{ (int) $stats['categories'] }}">{{ number_format($stats['categories']) }}</div>
                        <div class="about-stat__label">{{ __('about.stat_categories') }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="about-stat card-surface p-4 text-center h-100">
                        <div class="about-stat__value" data-count="{{ (int) $stats['guides'] }}">{{ number_format($stats['guides']) }}</div>
                        <div class="about-stat__label">{{ __('about.stat_guides') }}</div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-5 align-items-stretch">
                <div class="col-lg-7">
                    <div class="card-surface p-4 p-md-5 h-100">
                        <span class="eyebrow">{{ __('about.who_eyebrow') }}</span>
                        <h2 class="h3 mb-3">{{ __('about.who_title') }}</h2>
                        <p class="text-muted-custom">{{ __('about.who_p1') }}</p>
                        <p class="text-muted-custom mb-0">{{ __('about.who_p2') }}</p>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card-surface p-4 p-md-5 h-100">
                        <h2 class="h5 mb-3">{{ __('about.coverage_title') }}</h2>
                        <ul class="about-coverage list-unstyled mb-0">
                            @forelse($coverageCategories as $category)
                                <li>
                                    <a href="{{ route('categories.show', $category) }}" class="about-coverage__link">
                                        <i class="bi {{ $category->icon ?: 'bi-grid' }}"></i>
                                        <span class="about-coverage__name">{{ $category->name }}</span>
                                        <span class="about-coverage__count">{{ number_format($category->calculators_count) }}</span>
                                    </a>
                                </li>
                            @empty
                                <li class="text-muted-custom">{{ __('about.coverage_empty') }}</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <div class="section-heading mb-4">
                <span class="eyebrow">{{ __('about.values_eyebrow') }}</span>
                <h2>{{ __('about.values_title') }}</h2>
            </div>

            <div class="row g-3 g-lg-4 mb-5">
                <div class="col-md-4">
                    <article class="how-to-card">
                        <div class="how-to-card__icons">
                            <span class="how-to-card__glyph" aria-hidden="true"><i class="bi bi-bullseye"></i></span>
                        </div>
                        <h3>{{ __('about.mission_title') }}</h3>
                        <p>{{ __('about.mission_body') }}</p>
                    </article>
                </div>
                <div class="col-md-4">
                    <article class="how-to-card">
                        <div class="how-to-card__icons">
                            <span class="how-to-card__glyph" aria-hidden="true"><i class="bi bi-journal-text"></i></span>
                        </div>
                        <h3>{{ __('about.build_title') }}</h3>
                        <p>{{ __('about.build_body') }}</p>
                    </article>
                </div>
                <div class="col-md-4">
                    <article class="how-to-card">
                        <div class="how-to-card__icons">
                            <span class="how-to-card__glyph" aria-hidden="true"><i class="bi bi-shield-check"></i></span>
                        </div>
                        <h3>{{ __('about.trust_title') }}</h3>
                        <p>{!! __('about.trust_body', [
                            'privacy' => '<a href="'.e(route('privacy')).'">'.e(__('footer.privacy')).'</a>',
                            'cookies' => '<a href="'.e(route('cookies')).'">'.e(__('footer.cookies')).'</a>',
                        ]) !!}</p>
                    </article>
                </div>
            </div>

            <div class="card-surface p-4 p-md-5">
                <h2 class="h4 mb-3">{{ __('about.standards_title') }}</h2>
                <p class="text-muted-custom">{{ __('about.standards_p1') }}</p>
                <p class="text-muted-custom mb-4">{{ __('about.standards_p2') }}</p>
                <a href="{{ route('contact') }}" class="btn btn-brand me-2">{{ __('about.cta_contact') }}</a>
                <a href="{{ route('calculators.index') }}" class="btn btn-outline-brand me-2">{{ __('about.cta_browse') }}</a>
                <a href="{{ route('blog.index') }}" class="btn btn-outline-brand">{{ __('about.cta_blog') }}</a>
            </div>
        </div>
    </section>
@endsection
