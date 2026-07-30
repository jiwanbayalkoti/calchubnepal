@extends('layouts.public')

@php
    view()->share('meta', [
        'title' => 'Page Not Found — '.(app(\App\Services\Settings\AppSettings::class)->siteName()),
        'description' => 'The page you requested could not be found. Browse our free calculators, blog, or sitemap.',
        'robots' => 'noindex,follow',
        'canonical' => url('/'),
    ]);
@endphp

@section('content')
<section class="section atmosphere py-5">
    <div class="container text-center" style="max-width:640px;">
        <p class="display-4 fw-bold mb-2" style="color:var(--brand);">404</p>
        <h1 class="h3 mb-3">Page not found</h1>
        <p class="text-muted-custom mb-4">The page you requested does not exist or was moved. Try the sitemap or search.</p>
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a href="{{ route('home') }}" class="btn btn-brand">Home</a>
            <a href="{{ route('calculators.index') }}" class="btn btn-outline-brand">All Calculators</a>
            <a href="{{ route('sitemap') }}" class="btn btn-soft">Sitemap</a>
        </div>
    </div>
</section>
@endsection
