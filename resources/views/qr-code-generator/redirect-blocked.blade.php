@extends('layouts.public')

@section('content')
@php
    $ws = $qr->workspace;
    $white = $ws && $ws->white_label_enabled;
    $primary = $ws->brand_primary ?? '#0B6E4F';
@endphp
<section class="section atmosphere py-5">
    <div class="container" style="max-width: 480px;">
        <div class="card-surface p-4 p-md-5 text-center" @if($white) style="border-top: 4px solid {{ $primary }}" @endif>
            @if($white && $ws->logoUrl())
                <img src="{{ $ws->logoUrl() }}" alt="{{ $ws->name }}" class="mb-3" style="max-height:48px">
            @else
                <i class="bi bi-qr-code fs-1 text-brand mb-3 d-block"></i>
            @endif
            <h1 class="h4 mb-2">{{ $title }}</h1>
            <p class="text-muted-custom mb-0">{{ $message }}</p>
            @if($white && $ws->redirect_footer)
                <p class="small text-muted-custom mt-3 mb-0">{{ $ws->redirect_footer }}</p>
            @elseif($white && $ws->support_email)
                <p class="small text-muted-custom mt-3 mb-0">Support: {{ $ws->support_email }}</p>
            @else
                <a href="{{ route('qr-code-generator') }}" class="btn btn-brand mt-4">Create your own QR</a>
            @endif
        </div>
    </div>
</section>
@endsection
