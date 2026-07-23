@extends('layouts.advertiser')

@section('title', 'My Advertisements')
@section('page-title', 'My Advertisements')

@push('breadcrumbs')
    <li class="breadcrumb-item active">My Advertisements</li>
@endpush

@section('content')
    <div class="card">
        <div class="card-header"><span class="text-muted">Read-only view of ads assigned to {{ $advertiser->company_name }}</span></div>
        <div class="card-body">
            <table id="myAdsTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>Preview</th>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Size</th>
                        <th>URL</th>
                        <th>Status</th>
                        <th>Start</th>
                        <th>Expiry</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$('#myAdsTable').DataTable({
    ajax: '{{ route('advertiser.advertisements.data') }}',
    columns: [
        { data: 'image_url', orderable: false, render: (v) => v ? `<img src="${v}" style="height:40px;border-radius:4px;">` : '—' },
        { data: 'name' },
        { data: 'position' },
        { data: 'banner_size' },
        { data: 'link_url', render: (v) => v ? `<a href="${v}" target="_blank" rel="noopener">Open</a>` : '—' },
        { data: 'status', render: (v, t, row) => `<span class="badge badge-${row.is_running ? 'success' : 'secondary'}">${v}</span>` },
        { data: 'start_at', defaultContent: '—' },
        { data: 'end_at', defaultContent: '—' },
        { data: 'id', orderable: false, render: (id) => `<a class="btn btn-sm btn-primary" href="{{ url('advertiser/advertisements') }}/${id}">View Details</a>` },
    ]
});
</script>
@endpush
