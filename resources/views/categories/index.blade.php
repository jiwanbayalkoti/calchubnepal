@extends('layouts.public')

@section('breadcrumb')
    @include('partials.calculator.breadcrumb', ['breadcrumbs' => [
        ['name' => 'Home', 'url' => route('home')],
        ['name' => 'Categories', 'url' => route('categories.index')],
    ]])
@endsection

@section('content')
    <section class="section atmosphere pt-4">
        <div class="container">
            <div class="section-heading">
                <span class="eyebrow">Browse</span>
                <h2>All categories</h2>
            </div>

            <div class="row g-3">
                @foreach($categories as $category)
                    <div class="col-md-6 col-lg-3">
                        <a href="{{ route('categories.show', $category) }}" class="category-card">
                            <span class="cat-icon"><i class="bi {{ $category->icon ?? 'bi-grid' }}"></i></span>
                            <span>
                                <span class="d-block fw-semibold">{{ $category->name }}</span>
                                <span class="text-muted-custom small">{{ $category->calculators_count }} calculators</span>
                            </span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
