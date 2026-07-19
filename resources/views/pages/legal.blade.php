@extends('layouts.public')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $page->title }}</li>
        </ol>
    </nav>
@endsection

@section('content')
    <section class="section atmosphere pt-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <header class="mb-4">
                        <span class="hero-eyebrow"><i class="bi bi-shield-lock"></i> Legal</span>
                        <h1 class="h2 mb-2">{{ $page->title }}</h1>
                        <p class="text-muted-custom mb-0">Please read this page carefully. It explains how AI Calculator Hub works for you.</p>
                    </header>

                    <article class="card-surface p-4 p-md-5 legal-content">
                        {!! $page->content !!}
                    </article>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <a href="{{ route('privacy') }}" class="btn btn-sm {{ request()->routeIs('privacy') ? 'btn-brand' : 'btn-soft' }}">Privacy Policy</a>
                        <a href="{{ route('terms') }}" class="btn btn-sm {{ request()->routeIs('terms') ? 'btn-brand' : 'btn-soft' }}">Terms &amp; Conditions</a>
                        <a href="{{ route('contact') }}" class="btn btn-sm btn-outline-brand">Contact us</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
