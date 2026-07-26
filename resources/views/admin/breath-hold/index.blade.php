@extends('layouts.admin')

@section('title', 'Breath Hold Game')
@section('page-title', 'Breath Hold Reports')

@push('breadcrumbs')
    <li class="breadcrumb-item active">Breath Hold</li>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($stats['total']) }}</h3>
                    <p>Total Plays</p>
                </div>
                <div class="icon"><i class="fas fa-stopwatch"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($stats['today']) }}</h3>
                    <p>Plays Today</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-day"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($stats['certificates']) }}</h3>
                    <p>Certificates Issued</p>
                </div>
                <div class="icon"><i class="fas fa-certificate"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ number_format($stats['by_band']['healthy']) }}</h3>
                    <p>Healthy Band</p>
                </div>
                <div class="icon"><i class="fas fa-heart"></i></div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-danger"><i class="fas fa-exclamation"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Poor (&lt;20s)</span>
                    <span class="info-box-number">{{ number_format($stats['by_band']['poor']) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-minus"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Medium (20–40s)</span>
                    <span class="info-box-number">{{ number_format($stats['by_band']['medium']) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Healthy (≥40s)</span>
                    <span class="info-box-number">{{ number_format($stats['by_band']['healthy']) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-end gap-2">
            <select id="filterBand" class="form-control form-control-sm" style="width:160px;">
                <option value="">All bands</option>
                <option value="poor">Poor</option>
                <option value="medium">Medium</option>
                <option value="healthy">Healthy</option>
            </select>
            <div class="custom-control custom-checkbox mt-1 ml-2">
                <input type="checkbox" class="custom-control-input" id="filterCertified">
                <label class="custom-control-label" for="filterCertified">Certified only</label>
            </div>
        </div>
        <div class="card-body">
            <table id="breathHoldTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Duration</th>
                        <th>Band</th>
                        <th>Certificate</th>
                        <th>Played at</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    AdminCRUD.initDataTable('#breathHoldTable', {
        ajaxUrl: '{{ route("admin.breath-hold.data") }}',
        order: [[0, 'desc']],
        extraFilters: () => ({
            band: $('#filterBand').val(),
            certified_only: $('#filterCertified').is(':checked') ? 1 : 0,
        }),
        columns: [
            { data: 'id', name: 'id' },
            { data: 'user', name: 'user_id', orderable: false },
            { data: 'duration', name: 'duration_seconds' },
            { data: 'band', name: 'band' },
            { data: 'certificate', name: 'certificate_code' },
            { data: 'played_at', name: 'created_at' },
        ],
    });

    $('#filterBand, #filterCertified').on('change', () => AdminCRUD.reload());
});
</script>
@endpush
