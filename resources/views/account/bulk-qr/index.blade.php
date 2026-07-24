@extends('layouts.account')

@section('account')
    <h1 class="h3 mb-1">Bulk QR generation</h1>
    <p class="text-muted-custom mb-4">Upload a CSV (`title,destination_url`) — get a ZIP of PNG QRs + manifest. Limit: {{ $maxRows }} rows.</p>

    <div class="card-surface p-3 p-md-4 mb-4">
        <form method="POST" action="{{ route('account.bulk-qr.store') }}" enctype="multipart/form-data" class="row g-3">
            @csrf
            <div class="col-md-3"><input type="file" name="file" class="form-control" accept=".csv,text/csv" required></div>
            <div class="col-md-2">
                <select name="workspace_id" class="form-select">
                    <option value="">Workspace</option>
                    @foreach($workspaces as $ws)<option value="{{ $ws->id }}">{{ $ws->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="brand_template_id" class="form-select">
                    <option value="">Template</option>
                    @foreach($templates as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="campaign_id" class="form-select">
                    <option value="">Campaign</option>
                    @foreach($campaigns as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-brand w-100">Generate ZIP</button></div>
        </form>
        <p class="small text-muted-custom mt-2 mb-0">Excel: export sheet as CSV first. Dynamic short URLs are created for each row.</p>
    </div>

    <div class="card-surface p-3 p-md-4">
        @forelse($jobs as $job)
            <div class="account-list-item d-flex justify-content-between align-items-center">
                <div>
                    <strong>{{ $job->uuid }}</strong>
                    <div class="small text-muted-custom">{{ $job->status }} · {{ $job->processed_rows }}/{{ $job->total_rows }} ok · {{ $job->failed_rows }} failed</div>
                </div>
                @if($job->isReady())
                    <a href="{{ route('account.bulk-qr.download', $job) }}" class="btn btn-sm btn-brand">Download ZIP</a>
                @endif
            </div>
        @empty
            <p class="text-muted-custom mb-0">No bulk jobs yet.</p>
        @endforelse
        <div class="mt-3">{{ $jobs->links() }}</div>
    </div>
@endsection
