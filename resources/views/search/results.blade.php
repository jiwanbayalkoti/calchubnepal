@extends('layouts.public')

@section('breadcrumb')
    @include('partials.calculator.breadcrumb', ['breadcrumbs' => [
        ['name' => 'Home', 'url' => route('home')],
        ['name' => 'Search', 'url' => route('search.results')],
    ]])
@endsection

@section('content')
    <section class="section atmosphere pt-4">
        <div class="container">
            <div class="section-heading">
                <span class="eyebrow">Search</span>
                <h2>
                    @if($term)
                        Results for "{{ $term }}"
                    @else
                        Search calculators
                    @endif
                </h2>
            </div>

            <form method="GET" action="{{ route('search.results') }}" class="search-box mb-4" style="max-width: 480px;">
                <i class="bi bi-search"></i>
                <input type="search" name="q" value="{{ $term }}" class="form-control js-live-search" placeholder="Search calculators..." autofocus>
            </form>

            <div class="row g-3">
                @forelse($results as $calculator)
                    <div class="col-6 col-md-4 col-lg-3">
                        <a href="{{ route('calculators.show', $calculator) }}" class="calc-card">
                            <span class="calc-icon"><i class="bi {{ $calculator->icon ?? 'bi-calculator' }}"></i></span>
                            <p class="calc-title">{{ $calculator->title }}</p>
                            <p class="calc-desc">{{ $calculator->short_description }}</p>
                        </a>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-search fs-1 text-muted-custom"></i>
                        <p class="text-muted-custom mt-2">No calculators found. Try a different keyword.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-4 d-flex justify-content-center">
                {{ $results->withQueryString()->links() }}
            </div>
        </div>
    </section>
@endsection
