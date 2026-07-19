@extends('layouts.public')

@section('content')
    <section class="hero-section atmosphere text-center">
        <div class="container position-relative" style="z-index:2;">
            <span class="hero-eyebrow"><i class="bi bi-tag"></i> Pricing</span>
            <h1 class="hero-title mx-auto" style="font-size: clamp(2rem, 4vw, 3rem);">Simple pricing, no surprises.</h1>
            <p class="hero-subtitle">Start free. Upgrade whenever you need PDF exports, unlimited saves and priority AI explanations.</p>
        </div>
    </section>

    <section class="section pt-0">
        <div class="container">
            <div class="row g-4 justify-content-center">
                @forelse($plans as $index => $plan)
                    <div class="col-md-6 col-lg-4">
                        <div class="price-card {{ $index === 1 ? 'featured' : '' }}">
                            @if($index === 1)
                                <span class="badge-soft-accent mb-3 d-inline-block">Most Popular</span>
                            @endif
                            <h3 class="h5">{{ $plan->name }}</h3>
                            <p class="text-muted-custom small">{{ $plan->description }}</p>
                            <div class="price-amount">
                                {{ $plan->isFree() ? 'Free' : $plan->currency.' '.number_format((float) $plan->price, 0) }}
                                @if(!$plan->isFree())
                                    <span class="fs-6 text-muted-custom">/{{ $plan->billing_period }}</span>
                                @endif
                            </div>
                            <ul>
                                @foreach(($plan->features ?? []) as $feature)
                                    <li><i class="bi bi-check-circle-fill"></i> {{ $feature }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn {{ $index === 1 ? 'btn-brand' : 'btn-outline-brand' }} w-100 js-open-auth" data-auth="register">Get Started</button>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <p class="text-muted-custom">Pricing plans are being finalized. Please check back soon.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
