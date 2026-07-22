@extends('layouts.public')

@section('content')
    <section class="hero-section atmosphere text-center">
        <div class="container position-relative" style="z-index:2;">
            <span class="hero-eyebrow"><i class="bi bi-envelope"></i> Contact</span>
            <h1 class="hero-title mx-auto" style="font-size: clamp(2rem, 4vw, 3rem);">We'd love to hear from you.</h1>
            <p class="hero-subtitle">Questions, feedback, corrections or partnership ideas — send a message and our team will respond as soon as we can.</p>
        </div>
    </section>

    <section class="section pt-0">
        <div class="container">
            <div class="row g-4 justify-content-center">
                <div class="col-lg-7">
                    <div class="card-surface p-4 p-md-5">
                        @if(session('status'))
                            <div class="alert alert-success">{{ session('status') }}</div>
                        @endif

                        <form class="js-contact-form row g-3" action="{{ route('contact.store') }}" method="POST">
                            @csrf
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone (optional)</label>
                                <input type="text" name="phone" class="form-control">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Subject</label>
                                <input type="text" name="subject" class="form-control" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Message</label>
                                <textarea name="message" rows="5" class="form-control" required></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-brand"><i class="bi bi-send me-1"></i> Send Message</button>
                            </div>
                        </form>
                    </div>

                    <div class="card-surface p-4 p-md-5 mt-4">
                        <h2 class="h5 mb-1">Quick feedback</h2>
                        <p class="text-muted-custom small mb-3">Report a bug, request a feature, or share a short note. This goes to the admin inbox — not the contact mailbox.</p>
                        <form class="js-feedback-form row g-3" action="{{ route('feedback.store') }}" method="POST">
                            @csrf
                            <div class="col-md-6">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select">
                                    <option value="general">General</option>
                                    <option value="bug">Bug</option>
                                    <option value="feature">Feature request</option>
                                    <option value="calculator">Calculator</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rating (optional)</label>
                                <select name="rating" class="form-select">
                                    <option value="">—</option>
                                    @for ($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}">{{ $i }} star{{ $i > 1 ? 's' : '' }}</option>
                                    @endfor
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Your feedback</label>
                                <textarea name="message" rows="4" class="form-control" required maxlength="5000"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-outline-brand"><i class="bi bi-chat-dots me-1"></i> Send feedback</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card-surface p-4 h-100">
                        <h3 class="h5 mb-3">Get in touch</h3>
                        <p class="text-muted-custom">
                            <i class="bi bi-envelope me-2"></i>
                            <a href="mailto:{{ $hub->supportEmail() }}">{{ $hub->supportEmail() }}</a>
                        </p>
                        <p class="text-muted-custom"><i class="bi bi-geo-alt me-2"></i> {{ $hub->location() }}</p>
                        <hr class="divider-soft">
                        <p class="small text-muted-custom mb-3">For privacy requests, calculator corrections, or advertising / partnership enquiries, email us or use this form. We typically reply within 1–2 business days.</p>
                        <div class="social-icons">
                            @php
                                $socialIcons = [
                                    'facebook' => 'bi-facebook',
                                    'twitter' => 'bi-twitter-x',
                                    'linkedin' => 'bi-linkedin',
                                    'youtube' => 'bi-youtube',
                                    'tiktok' => 'bi-tiktok',
                                ];
                            @endphp
                            @foreach ($hub->socialLinks() as $network => $url)
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ ucfirst($network) }}"><i class="bi {{ $socialIcons[$network] ?? 'bi-link-45deg' }}"></i></a>
                            @endforeach
                        </div>
                        <hr class="divider-soft">
                        <p class="small mb-0">
                            <a href="{{ route('privacy') }}">Privacy</a> ·
                            <a href="{{ route('cookies') }}">Cookies</a> ·
                            <a href="{{ route('terms') }}">Terms</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
