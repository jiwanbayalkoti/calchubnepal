@extends('layouts.public')

@push('schemas')
    <script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('breadcrumb')
    @include('partials.calculator.breadcrumb', ['breadcrumbs' => $breadcrumbs])
@endsection

@section('content')
    <section class="section atmosphere pt-4">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8">
                    <span class="badge-soft-brand mb-2 d-inline-block">{{ $post->category?->name ?? 'General' }}</span>
                    <h1 class="mb-2">{{ $post->title }}</h1>
                    <p class="reading-meta mb-4">
                        <i class="bi bi-person-circle"></i> {{ $post->author?->name ?? 'AI Calculator Hub Team' }}
                        &middot; <i class="bi bi-calendar3"></i> {{ $post->published_at?->format('M d, Y') }}
                        &middot; <i class="bi bi-clock"></i> {{ $post->reading_time_minutes }} min read
                    </p>

                    @if($post->featured_image)
                        <img src="{{ asset('storage/'.$post->featured_image) }}" class="w-100 mb-4" style="max-height:420px;object-fit:cover;border-radius:var(--radius-md);" alt="{{ $post->title }}">
                    @endif

                    <div class="blog-content">
                        {!! $post->content !!}
                    </div>

                    <div class="d-flex align-items-center justify-content-between mt-5 no-print">
                        @include('partials.calculator.share-buttons', ['calculator' => $post])
                    </div>

                    @if($post->calculators->isNotEmpty())
                        <section class="mt-5">
                            <h3 class="h5 mb-3">Calculators mentioned in this article</h3>
                            <div class="row g-3">
                                @foreach($post->calculators as $calc)
                                    <div class="col-6 col-md-3">
                                        <a href="{{ route('calculators.show', $calc) }}" class="calc-card h-100">
                                            <span class="calc-icon"><i class="bi {{ $calc->icon ?? 'bi-calculator' }}"></i></span>
                                            <p class="calc-title">{{ $calc->title }}</p>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if($related->isNotEmpty())
                        <section class="mt-5">
                            <h3 class="h5 mb-3">Related articles</h3>
                            <div class="row g-4">
                                @foreach($related as $item)
                                    <div class="col-md-4">
                                        <a href="{{ route('blog.show', $item) }}" class="card-surface blog-card d-block h-100 text-decoration-none p-3">
                                            <h4 class="h6" style="color: var(--ink);">{{ $item->title }}</h4>
                                            <p class="reading-meta mb-0">{{ $item->reading_time_minutes }} min read</p>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>

                <div class="col-lg-4">
                    @if(!empty($toc))
                        <div class="toc-box mb-3">
                            <h6 class="text-uppercase small fw-bold text-muted-custom mb-3">On this page</h6>
                            @foreach($toc as $item)
                                <a href="#{{ $item['id'] }}" class="{{ $item['level'] === 3 ? 'ps-3' : '' }}">{{ $item['text'] }}</a>
                            @endforeach
                        </div>
                    @endif

                    @include('partials.ads.sidebar')
                </div>
            </div>
        </div>
    </section>
@endsection
