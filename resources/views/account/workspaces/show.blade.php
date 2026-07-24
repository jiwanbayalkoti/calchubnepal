@extends('layouts.account')

@section('account')
    <div class="mb-4">
        <a href="{{ route('account.workspaces.index') }}" class="small text-muted-custom text-decoration-none">&larr; Workspaces</a>
        <h1 class="h3 mb-1 mt-1">{{ $workspace->name }}</h1>
        <p class="text-muted-custom mb-0">Role: {{ $role?->label() }}{{ $workspace->white_label_enabled ? ' · White label on' : '' }}</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-4"><div class="account-stat card-surface p-3"><div class="stat-label">QRs</div><div class="stat-value">{{ $report['qr_count'] }}</div></div></div>
        <div class="col-4"><div class="account-stat card-surface p-3"><div class="stat-label">Scans</div><div class="stat-value">{{ number_format($report['total_scans']) }}</div></div></div>
        <div class="col-4"><div class="account-stat card-surface p-3"><div class="stat-label">Today</div><div class="stat-value">{{ number_format($report['today_scans']) }}</div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card-surface p-3 p-md-4 mb-4">
                <h2 class="h6 mb-3">Branding & white label</h2>
                <form method="POST" action="{{ route('account.workspaces.update', $workspace) }}">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ $workspace->name }}" required></div>
                        <div class="col-md-3"><label class="form-label">Primary</label><input type="color" name="brand_primary" class="form-control form-control-color w-100" value="{{ $workspace->brand_primary }}"></div>
                        <div class="col-md-3"><label class="form-label">Secondary</label><input type="color" name="brand_secondary" class="form-control form-control-color w-100" value="{{ $workspace->brand_secondary }}"></div>
                        <div class="col-md-6"><label class="form-label">Support email</label><input type="email" name="support_email" class="form-control" value="{{ $workspace->support_email }}"></div>
                        <div class="col-md-6"><label class="form-label">Custom domain</label><input name="custom_domain" class="form-control" value="{{ $workspace->custom_domain }}" placeholder="qr.yourbrand.com"></div>
                        <div class="col-12"><label class="form-label">Redirect footer</label><textarea name="redirect_footer" class="form-control" rows="2">{{ $workspace->redirect_footer }}</textarea></div>
                        <div class="col-12 form-check"><input class="form-check-input" type="checkbox" name="white_label_enabled" value="1" id="wl2" @checked($workspace->white_label_enabled)><label class="form-check-label" for="wl2">Enable white label (hide Calchub branding on unlock/blocked pages)</label></div>
                        <div class="col-12"><button class="btn btn-brand btn-sm">Save branding</button></div>
                    </div>
                </form>
            </div>

            <div class="card-surface p-3 p-md-4">
                <h2 class="h6 mb-3">Heat map</h2>
                <div class="qr-heat-map">
                    @forelse($report['heat_map'] as $point)
                        <div class="qr-heat-point" style="--lat: {{ $point['lat'] }}; --lng: {{ $point['lng'] }}; --w: {{ min(48, 12 + $point['scans']) }}px;" title="{{ $point['country'] }}: {{ $point['scans'] }}"></div>
                    @empty
                        <p class="small text-muted-custom mb-0">No scans yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card-surface p-3 p-md-4 mb-4">
                <h2 class="h6 mb-3">Invite teammate</h2>
                <form method="POST" action="{{ route('account.workspaces.invite', $workspace) }}" class="row g-2">
                    @csrf
                    <div class="col-12"><input type="email" name="email" class="form-control" placeholder="email@company.com" required></div>
                    <div class="col-8">
                        <select name="role" class="form-select">
                            <option value="admin">Admin</option>
                            <option value="member" selected>Member</option>
                            <option value="viewer">Viewer</option>
                        </select>
                    </div>
                    <div class="col-4"><button class="btn btn-outline-brand w-100">Invite</button></div>
                </form>
            </div>
            <div class="card-surface p-3 p-md-4">
                <h2 class="h6 mb-3">Team</h2>
                @foreach($workspace->members as $member)
                    <div class="d-flex justify-content-between align-items-center gap-2 py-2 border-bottom">
                        <div class="small">
                            <strong>{{ $member->user?->name ?? $member->invited_email }}</strong>
                            <div class="text-muted-custom">{{ $member->role }}</div>
                        </div>
                        @if($member->user_id != $workspace->owner_id)
                            <form method="POST" action="{{ route('account.workspaces.members.destroy', [$workspace, $member]) }}" onsubmit="return confirm('Remove member?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-soft">Remove</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
