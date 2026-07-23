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

                    <div class="blog-content" id="blogArticleContent">
                        {!! $contentHtml !!}
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

                <div class="col-lg-4 blog-sidebar-col">
                    <aside class="blog-sidebar-sticky">
                        @if(!empty($toc))
                            <nav class="toc-box" aria-label="On this page">
                                <h6 class="text-uppercase small fw-bold text-muted-custom mb-3">On this page</h6>
                                <div class="toc-box__links">
                                    @foreach($toc as $item)
                                        <a href="#{{ $item['id'] }}" class="toc-link{{ $item['level'] === 3 ? ' ps-3' : '' }}" data-toc-target="{{ $item['id'] }}">{{ $item['text'] }}</a>
                                    @endforeach
                                </div>
                            </nav>
                        @endif

                        @include('partials.ads.sidebar')
                    </aside>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
(function () {
    const tocLinks = document.querySelectorAll('.toc-box .toc-link');
    if (!tocLinks.length) return;

    const clearHighlights = () => {
        document.querySelectorAll('.blog-content .is-highlighted').forEach((el) => {
            el.classList.remove('is-highlighted');
        });
        tocLinks.forEach((link) => link.classList.remove('is-active'));
    };

    const highlightTarget = (id, link) => {
        const target = document.getElementById(id);
        if (!target) return;

        clearHighlights();
        target.classList.add('is-highlighted');
        if (link) link.classList.add('is-active');

        target.scrollIntoView({ behavior: 'smooth', block: 'start' });

        window.clearTimeout(highlightTarget._timer);
        highlightTarget._timer = window.setTimeout(() => {
            target.classList.remove('is-highlighted');
        }, 2200);
    };

    tocLinks.forEach((link) => {
        link.addEventListener('click', (e) => {
            const id = link.getAttribute('data-toc-target') || (link.getAttribute('href') || '').replace(/^#/, '');
            if (!id || !document.getElementById(id)) return;
            e.preventDefault();
            highlightTarget(id, link);
            if (history.replaceState) {
                history.replaceState(null, '', '#' + id);
            }
        });
    });

    if (location.hash) {
        const id = location.hash.slice(1);
        const link = document.querySelector('.toc-box .toc-link[data-toc-target="' + CSS.escape(id) + '"]');
        if (document.getElementById(id)) {
            window.setTimeout(() => highlightTarget(id, link), 80);
        }
    }
})();
</script>
@endpush
