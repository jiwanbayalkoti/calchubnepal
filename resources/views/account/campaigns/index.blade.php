@extends('layouts.account')

@section('account')
    <h1 class="h3 mb-1">Campaigns</h1>
    <p class="text-muted-custom mb-4">Track QR performance by campaign with UTM tags.</p>

    <div class="card-surface p-3 p-md-4 mb-4">
        <form method="POST" action="{{ route('account.campaigns.store') }}" class="row g-3">
            @csrf
            <div class="col-md-4"><input name="name" class="form-control" placeholder="Campaign name" required></div>
            <div class="col-md-2"><input name="utm_source" class="form-control" placeholder="utm_source"></div>
            <div class="col-md-2"><input name="utm_medium" class="form-control" placeholder="utm_medium"></div>
            <div class="col-md-2"><input name="utm_campaign" class="form-control" placeholder="utm_campaign"></div>
            <div class="col-md-2"><button class="btn btn-brand w-100">Create</button></div>
        </form>
    </div>

    <div class="card-surface p-3 p-md-4">
        @forelse($campaigns as $c)
            <div class="account-list-item d-flex justify-content-between align-items-center">
                <div>
                    <a href="{{ route('account.campaigns.show', $c) }}" class="fw-semibold text-decoration-none">{{ $c->name }}</a>
                    <div class="small text-muted-custom">{{ $c->qr_codes_count }} QRs · {{ $c->status }}</div>
                </div>
                <a href="{{ route('account.campaigns.show', $c) }}" class="btn btn-sm btn-soft">Analytics</a>
            </div>
        @empty
            <p class="text-muted-custom mb-0">No campaigns yet.</p>
        @endforelse
        <div class="mt-3">{{ $campaigns->links() }}</div>
    </div>
@endsection
