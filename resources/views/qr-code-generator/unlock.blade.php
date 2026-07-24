@extends('layouts.public')

@section('content')
@php
    $ws = $qr->workspace;
    $white = $ws && $ws->white_label_enabled;
    $primary = $ws->brand_primary ?? '#0B6E4F';
@endphp
<section class="section atmosphere py-5">
    <div class="container" style="max-width: 480px;">
        <div class="card-surface p-4 p-md-5" @if($white) style="border-top: 4px solid {{ $primary }}" @endif>
            <div class="text-center mb-4">
                @if($white && $ws->logoUrl())
                    <img src="{{ $ws->logoUrl() }}" alt="{{ $ws->name }}" class="mb-2" style="max-height:48px">
                @else
                    <i class="bi bi-shield-lock fs-1 text-brand d-block mb-2"></i>
                @endif
                <h1 class="h4 mb-1">Password protected QR</h1>
                <p class="text-muted-custom mb-0">Enter the password to continue.</p>
            </div>

            @if(!empty($error))
                <div class="alert alert-danger">{{ $error }}</div>
            @endif

            <form method="POST" action="{{ route('qr.unlock.submit', $code) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="qr_unlock_password">Password</label>
                    <input type="password" name="password" id="qr_unlock_password" class="form-control @error('password') is-invalid @enderror" required autofocus>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-brand w-100" @if($white) style="background:{{ $primary }};border-color:{{ $primary }}" @endif>Unlock & continue</button>
            </form>

            @if($white && $ws->redirect_footer)
                <p class="small text-muted-custom text-center mt-3 mb-0">{{ $ws->redirect_footer }}</p>
            @endif
        </div>
    </div>
</section>
@endsection
