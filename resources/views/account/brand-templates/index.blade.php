@extends('layouts.account')

@section('account')
    <h1 class="h3 mb-1">Brand templates</h1>
    <p class="text-muted-custom mb-4">Save reusable QR styles (colors, modules, frames, logo) for campaigns and bulk jobs.</p>

    <div class="card-surface p-3 p-md-4 mb-4">
        <form method="POST" action="{{ route('account.brand-templates.store') }}" enctype="multipart/form-data" class="row g-3">
            @csrf
            <div class="col-md-4"><input name="name" class="form-control" placeholder="Template name" required></div>
            <div class="col-md-2"><input type="color" name="foreground" class="form-control form-control-color w-100" value="#0B6E4F"></div>
            <div class="col-md-2"><input type="color" name="background" class="form-control form-control-color w-100" value="#FFFFFF"></div>
            <div class="col-md-2">
                <select name="module_style" class="form-select">
                    <option value="square">Square</option>
                    <option value="rounded">Rounded</option>
                    <option value="dots">Dots</option>
                </select>
            </div>
            <div class="col-md-2"><input type="file" name="logo" class="form-control" accept="image/*"></div>
            <div class="col-md-3">
                <select name="workspace_id" class="form-select">
                    <option value="">Personal (no workspace)</option>
                    @foreach($workspaces as $ws)<option value="{{ $ws->id }}">{{ $ws->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3 form-check mt-2"><input class="form-check-input" type="checkbox" name="is_default" value="1" id="tdef"><label for="tdef" class="form-check-label">Default</label></div>
            <div class="col-md-3"><button class="btn btn-brand">Save template</button></div>
        </form>
    </div>

    <div class="card-surface p-3 p-md-4">
        @forelse($templates as $t)
            <div class="account-list-item d-flex justify-content-between align-items-center">
                <div>
                    <strong>{{ $t->name }}</strong>
                    @if($t->is_default)
                        <span class="badge bg-light text-dark border ms-1">Default</span>
                    @endif
                    <div class="small text-muted-custom">{{ $t->style_json['foreground'] ?? '' }} / {{ $t->style_json['module_style'] ?? '' }}</div>
                </div>
                <form method="POST" action="{{ route('account.brand-templates.destroy', $t) }}" onsubmit="return confirm('Delete template?')">@csrf @method('DELETE')<button class="btn btn-sm btn-soft">Delete</button></form>
            </div>
        @empty
            <p class="text-muted-custom mb-0">No templates yet.</p>
        @endforelse
    </div>
@endsection
