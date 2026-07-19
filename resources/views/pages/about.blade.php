@extends('layouts.public')

@section('content')
    <section class="hero-section atmosphere text-center">
        <div class="container position-relative" style="z-index:2;">
            <span class="hero-eyebrow"><i class="bi bi-info-circle"></i> About Us</span>
            <h1 class="hero-title mx-auto" style="font-size: clamp(2rem, 4vw, 3rem);">Calculators built for clarity.</h1>
            <p class="hero-subtitle">AI Calculator Hub is an independent tools platform that helps people make better everyday decisions with transparent formulas, worked examples and optional AI explanations.</p>
        </div>
    </section>

    <section class="section pt-0">
        <div class="container">
            <div class="row g-4 mb-5">
                <div class="col-lg-8">
                    <div class="card-surface p-4 p-md-5">
                        <h2 class="h4 mb-3">Who we are</h2>
                        <p class="text-muted-custom">AI Calculator Hub was created to solve a simple problem: most online calculators show a number without explaining the method, units or assumptions. We build free calculators for construction, finance, health, education, Nepal-specific needs and more — each with clear inputs, formulas and FAQs so you can trust the result.</p>
                        <p class="text-muted-custom mb-0">We operate from Kathmandu, Nepal, and serve users worldwide. Whether you are estimating materials for a build, planning a loan EMI, checking BMI, or converting land units, our goal is the same: accurate tools, plain language, and no unnecessary friction.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card-surface p-4 h-100">
                        <h2 class="h5 mb-3">At a glance</h2>
                        <ul class="list-unstyled text-muted-custom mb-0">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-brand me-2"></i>180+ free calculators</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-brand me-2"></i>Guides &amp; worked examples</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-brand me-2"></i>Optional AI explanations</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-brand me-2"></i>Save &amp; favorite tools</li>
                            <li class="mb-0"><i class="bi bi-check-circle-fill text-brand me-2"></i>Privacy-first accounts</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card-surface p-4 h-100">
                        <span class="calc-icon mb-3"><i class="bi bi-bullseye"></i></span>
                        <h3 class="h5">Our mission</h3>
                        <p class="text-muted-custom mb-0">Make accurate, transparent calculations free and accessible — so students, homeowners, builders and professionals can decide with confidence.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-surface p-4 h-100">
                        <span class="calc-icon mb-3"><i class="bi bi-journal-text"></i></span>
                        <h3 class="h5">How we build</h3>
                        <p class="text-muted-custom mb-0">Each tool documents its approach, common inputs and FAQs. We prefer standard industry methods and update tools when formulas or local rules change.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-surface p-4 h-100">
                        <span class="calc-icon mb-3"><i class="bi bi-shield-check"></i></span>
                        <h3 class="h5">Trust &amp; advertising</h3>
                        <p class="text-muted-custom mb-0">We may show ads (including Google AdSense) to keep tools free. Ads never change calculator math. See our <a href="{{ route('privacy') }}">Privacy</a> and <a href="{{ route('cookies') }}">Cookie</a> policies.</p>
                    </div>
                </div>
            </div>

            <div class="card-surface p-4 p-md-5">
                <h2 class="h4 mb-3">Editorial standards</h2>
                <p class="text-muted-custom">Calculator outputs are informational. They are not a substitute for licensed engineering, medical, legal, tax or financial advice. When a decision is high-stakes, verify with a qualified professional in your jurisdiction.</p>
                <p class="text-muted-custom mb-4">Found an error or have a tool request? We welcome feedback — it helps us improve accuracy for everyone.</p>
                <a href="{{ route('contact') }}" class="btn btn-brand me-2">Contact us</a>
                <a href="{{ route('calculators.index') }}" class="btn btn-outline-brand">Browse calculators</a>
            </div>
        </div>
    </section>
@endsection
