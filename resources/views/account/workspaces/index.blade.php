@extends('layouts.account')

@section('account')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Workspaces & teams</h1>
            <p class="text-muted-custom mb-0">Collaborate on QR codes with role-based access and white-label branding.</p>
        </div>
    </div>

    @unless($canCreate)
        <div class="alert alert-warning">Workspaces require Premium. <a href="{{ route('account.subscription') }}">Upgrade your plan</a>.</div>
    @else
        <div class="card-surface p-3 p-md-4 mb-4">
            <h2 class="h6 mb-3">Create workspace</h2>
            <form method="POST" action="{{ route('account.workspaces.store') }}" class="row g-3">
                @csrf
                <div class="col-md-4"><input type="text" name="name" class="form-control" placeholder="Workspace name" required></div>
                <div class="col-md-2"><input type="color" name="brand_primary" class="form-control form-control-color w-100" value="#0B6E4F"></div>
                <div class="col-md-2"><input type="color" name="brand_secondary" class="form-control form-control-color w-100" value="#F4A259"></div>
                <div class="col-md-2 form-check mt-2"><input class="form-check-input" type="checkbox" name="white_label_enabled" value="1" id="wl"><label class="form-check-label" for="wl">White label</label></div>
                <div class="col-md-2"><button class="btn btn-brand w-100">Create</button></div>
            </form>
        </div>
    @endunless

    <div class="card-surface p-3 p-md-4">
        @forelse($workspaces as $ws)
            <div class="account-list-item d-flex justify-content-between align-items-center gap-2">
                <div>
                    <a href="{{ route('account.workspaces.show', $ws) }}" class="fw-semibold text-decoration-none">{{ $ws->name }}</a>
                    <div class="small text-muted-custom">{{ $ws->members_count }} members · {{ $ws->qr_codes_count }} QRs{{ $ws->white_label_enabled ? ' · White label' : '' }}</div>
                </div>
                <a href="{{ route('account.workspaces.show', $ws) }}" class="btn btn-sm btn-soft">Open</a>
            </div>
        @empty
            <p class="text-muted-custom mb-0">No workspaces yet.</p>
        @endforelse
    </div>
@endsection
