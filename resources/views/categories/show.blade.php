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
            <div class="d-flex align-items-center gap-3 mb-4">
                <span class="cat-icon" style="width:60px;height:60px;"><i class="bi {{ $category->icon ?? 'bi-grid' }} fs-4"></i></span>
                <div>
                    <h1 class="h3 mb-1">{{ $category->name }}</h1>
                    <p class="text-muted-custom mb-0">{{ $category->description }}</p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-9">
                    <div class="row g-3">
                        @forelse($calculators as $calculator)
                            <div class="col-6 col-md-4">
                                <a href="{{ route('calculators.show', $calculator) }}" class="calc-card">
                                    <span class="calc-icon"><i class="bi {{ $calculator->icon ?? 'bi-calculator' }}"></i></span>
                                    <p class="calc-title">{{ $calculator->title }}</p>
                                    <p class="calc-desc">{{ $calculator->short_description }}</p>
                                </a>
                            </div>
                        @empty
                            <div class="col-12 text-center py-5">
                                <p class="text-muted-custom">No calculators in this category yet.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-4 d-flex justify-content-center">
                        {{ $calculators->withQueryString()->links() }}
                    </div>
                </div>

                <div class="col-lg-3">
                    @include('partials.ads.sidebar')

                    <div class="card-surface p-3">
                        <h6 class="text-uppercase small fw-bold text-muted-custom mb-3">Other categories</h6>
                        <ul class="list-unstyled mb-0">
                            @foreach($categories as $other)
                                <li class="mb-2">
                                    <a href="{{ route('categories.show', $other) }}" class="{{ $other->id === $category->id ? 'fw-bold text-brand' : '' }}">
                                        <i class="bi {{ $other->icon ?? 'bi-grid' }} me-1"></i> {{ $other->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
