@extends('layouts.public')

@section('breadcrumb')
    @include('partials.calculator.breadcrumb', ['breadcrumbs' => [
        ['name' => 'Home', 'url' => route('home')],
        ['name' => 'Blog', 'url' => route('blog.index')],
    ]])
@endsection

@section('content')
    <section class="section atmosphere pt-4">
        <div class="container">
            <div class="section-heading">
                <span class="eyebrow">Insights</span>
                <h2>Guides &amp; explainers</h2>
            </div>

            @if($featured)
                <a href="{{ route('blog.show', $featured) }}" class="card-surface d-block mb-5 text-decoration-none overflow-hidden">
                    <div class="row g-0">
                        @if($featured->featured_image)
                            <div class="col-md-5">
                                <img src="{{ asset('storage/'.$featured->featured_image) }}" class="w-100 h-100" style="object-fit:cover;min-height:220px;" alt="{{ $featured->title }}">
                            </div>
                        @endif
                        <div class="col-md-7 p-4 d-flex flex-column justify-content-center">
                            <span class="badge-soft-accent mb-2" style="width:fit-content;">Featured</span>
                            <h3 class="h4" style="color: var(--ink);">{{ $featured->title }}</h3>
                            <p class="text-muted-custom">{{ $featured->excerpt }}</p>
                            <p class="reading-meta mb-0"><i class="bi bi-clock"></i> {{ $featured->reading_time_minutes }} min read &middot; {{ $featured->published_at?->format('M d, Y') }}</p>
                        </div>
                    </div>
                </a>
            @endif

            <div class="row g-4">
                <div class="col-lg-9">
                    <div class="row g-4">
                        @forelse($posts as $post)
                            <div class="col-md-4">
                                <a href="{{ route('blog.show', $post) }}" class="card-surface blog-card d-block h-100 text-decoration-none overflow-hidden">
                                    @if($post->featured_image)
                                        <img src="{{ asset('storage/'.$post->featured_image) }}" class="w-100" style="height:170px;object-fit:cover;" alt="{{ $post->title }}">
                                    @endif
                                    <div class="p-3">
                                        <span class="badge-soft-brand mb-2 d-inline-block">{{ $post->category?->name ?? 'General' }}</span>
                                        <h3 class="h6 mb-2" style="color: var(--ink);">{{ $post->title }}</h3>
                                        <p class="reading-meta mb-0"><i class="bi bi-clock"></i> {{ $post->reading_time_minutes }} min read</p>
                                    </div>
                                </a>
                            </div>
                        @empty
                            <div class="col-12 text-center py-5">
                                <p class="text-muted-custom">No articles published yet. Check back soon!</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-4 d-flex justify-content-center">
                        {{ $posts->withQueryString()->links() }}
                    </div>
                </div>

                <div class="col-lg-3">
                    @include('partials.ads.sidebar')

                    <div class="card-surface p-3">
                        <h6 class="text-uppercase small fw-bold text-muted-custom mb-3">Categories</h6>
                        <ul class="list-unstyled mb-0">
                            @foreach($categories as $category)
                                <li class="mb-2">
                                    <a href="{{ route('blog.index', ['category' => $category->slug]) }}">{{ $category->name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
