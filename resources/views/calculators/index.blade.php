@extends('layouts.public')

@section('breadcrumb')
    @include('partials.calculator.breadcrumb', ['breadcrumbs' => [
        ['name' => 'Home', 'url' => route('home')],
        ['name' => 'Calculators', 'url' => route('calculators.index')],
    ]])
@endsection

@section('content')
    <section class="section atmosphere pt-4">
        <div class="container">
            <div class="section-heading">
                <span class="eyebrow">All calculators</span>
                <h2>Find the right calculator</h2>
            </div>

            <form method="GET" action="{{ route('calculators.index') }}" class="row g-2 align-items-center mb-4">
                <div class="col-md-5">
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="search" name="q" value="{{ $searchTerm }}" class="form-control js-live-search" placeholder="Search calculators...">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="category" class="form-select js-select2">
                        <option value="">All categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" {{ $activeCategory === $category->slug ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-brand w-100">Filter</button>
                </div>
            </form>

            <div class="row g-3">
                @forelse($calculators as $calculator)
                    <div class="col-6 col-md-4 col-lg-3">
                        <a href="{{ route('calculators.show', $calculator) }}" class="calc-card">
                            <span class="calc-icon"><i class="bi {{ $calculator->icon ?? 'bi-calculator' }}"></i></span>
                            <p class="calc-title">{{ $calculator->title }}</p>
                            <p class="calc-desc">{{ $calculator->short_description }}</p>
                            @if($calculator->is_premium)
                                <span class="calc-badge badge-soft-accent">Premium</span>
                            @else
                                <span class="calc-badge badge-soft-brand">Free</span>
                            @endif
                        </a>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-emoji-frown fs-1 text-muted-custom"></i>
                        <p class="text-muted-custom mt-2">No calculators matched your search. Try another keyword.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-4 d-flex justify-content-center">
                {{ $calculators->withQueryString()->links() }}
            </div>
        </div>
    </section>
@endsection
