@extends('layouts.public')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Sitemap</li>
        </ol>
    </nav>
@endsection

@section('content')
    <section class="section atmosphere pt-4">
        <div class="container">
            <header class="mb-4">
                <span class="hero-eyebrow"><i class="bi bi-diagram-3"></i> Sitemap</span>
                <h1 class="h2 mb-2">Browse the site</h1>
                <p class="text-muted-custom mb-0">
                    Find every main page, free tool, and calculator category.
                    Prefer XML for search engines?
                    <a href="{{ route('sitemap.xml') }}">Download sitemap.xml</a>
                </p>
            </header>

            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card-surface p-4 h-100">
                        <h2 class="h5 mb-3">Main pages</h2>
                        <ul class="list-unstyled sitemap-list mb-0">
                            @foreach ($mainLinks as $link)
                                <li class="mb-2"><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card-surface p-4 h-100">
                        <h2 class="h5 mb-3">Free tools</h2>
                        <ul class="list-unstyled sitemap-list mb-0">
                            @foreach ($toolLinks as $link)
                                <li class="mb-2"><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card-surface p-4 h-100">
                        <h2 class="h5 mb-3">Legal &amp; account</h2>
                        <ul class="list-unstyled sitemap-list mb-0">
                            @foreach ($legalLinks as $link)
                                <li class="mb-2"><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card-surface p-4 h-100">
                        <h2 class="h5 mb-3">Categories</h2>
                        <ul class="list-unstyled sitemap-list mb-0">
                            @forelse ($categories as $category)
                                <li class="mb-2">
                                    <a href="{{ route('categories.show', $category) }}">{{ $category->name }}</a>
                                    <span class="text-muted-custom small">({{ $category->calculators_count }})</span>
                                </li>
                            @empty
                                <li class="text-muted-custom">No categories yet.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card-surface p-4 mt-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h2 class="h5 mb-0">All calculators</h2>
                    <a href="{{ route('calculators.index') }}" class="small">View gallery</a>
                </div>
                <div class="row g-2">
                    @foreach ($calculators as $calculator)
                        <div class="col-sm-6 col-lg-4">
                            <a href="{{ route('calculators.show', $calculator) }}" class="sitemap-calc-link">
                                <i class="bi {{ $calculator->icon ?? 'bi-calculator' }}"></i>
                                <span>{{ $calculator->title }}</span>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($posts->isNotEmpty())
                <div class="card-surface p-4 mt-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <h2 class="h5 mb-0">Latest blog posts</h2>
                        <a href="{{ route('blog.index') }}" class="small">All posts</a>
                    </div>
                    <ul class="list-unstyled sitemap-list mb-0">
                        @foreach ($posts as $post)
                            <li class="mb-2"><a href="{{ route('blog.show', $post) }}">{{ $post->title }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </section>
@endsection
