@extends('layouts.admin')

@section('title', 'Feedback')
@section('page-title', 'User Feedback')

@push('breadcrumbs')
    <li class="breadcrumb-item active">Feedback</li>
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-end">
            <select id="filterStatus" class="form-control form-control-sm" style="width:180px;">
                <option value="">All Status</option>
                <option value="new">New</option>
                <option value="reviewed">Reviewed</option>
                <option value="resolved">Resolved</option>
            </select>
        </div>
        <div class="card-body">
            <table id="feedbackTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Calculator</th>
                        <th>Rating</th>
                        <th>Message</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th style="width:200px;">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="feedbackModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="feedbackForm" autocomplete="off">
                    <input type="hidden" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title">Feedback Detail</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <dl class="row">
                            <dt class="col-4">User</dt><dd class="col-8" id="fb_user"></dd>
                            <dt class="col-4">Calculator</dt><dd class="col-8" id="fb_calculator"></dd>
                            <dt class="col-4">Rating</dt><dd class="col-8" id="fb_rating"></dd>
                            <dt class="col-4">Type</dt><dd class="col-8" id="fb_type"></dd>
                        </dl>
                        <p id="fb_message" class="border rounded p-2 bg-light"></p>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control" required>
                                <option value="new">New</option>
                                <option value="reviewed">Reviewed</option>
                                <option value="resolved">Resolved</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    AdminCRUD.initDataTable('#feedbackTable', {
        ajaxUrl: '{{ route("admin.feedback.data") }}',
        order: [[6, 'desc']],
        extraFilters: () => ({ status: $('#filterStatus').val() }),
        columns: [
            { data: 'user', name: 'user_id' },
            { data: 'calculator', name: 'calculator_id', orderable: false, defaultContent: '-' },
            { data: 'rating', name: 'rating', defaultContent: '-' },
            { data: 'message', name: 'message' },
            { data: 'type', name: 'type' },
            {
                data: 'status', name: 'status',
                render: (v) => ({ new: 'warning', reviewed: 'info', resolved: 'success' }[v] ? `<span class="badge badge-${({new:'warning',reviewed:'info',resolved:'success'})[v]}">${v}</span>` : v),
            },
            { data: 'created_at', name: 'created_at' },
            {
                data: null, orderable: false, searchable: false,
                render: (row) => `
                    <div class="table-actions">
                        <button class="btn btn-sm btn-info btn-view" data-id="${row.id}"><i class="fas fa-eye"></i> View</button>
                        <button class="btn btn-sm btn-danger btn-delete" data-id="${row.id}"><i class="fas fa-trash"></i></button>
                    </div>`,
            },
        ],
    });

    $('#filterStatus').on('change', () => AdminCRUD.reload());

    AdminCRUD.bindEdit('.btn-view', (id) => `{{ url('admin/feedback') }}/${id}`, '#feedbackForm', '#feedbackModal', function (data) {
        $('#fb_user').text(data.user ? data.user.name : 'Guest');
        $('#fb_calculator').text(data.calculator ? data.calculator.title : '-');
        $('#fb_rating').text(data.rating ? data.rating + ' / 5' : '-');
        $('#fb_type').text(data.type);
        $('#fb_message').text(data.message);
        $('#feedbackForm [name=status]').val(data.status);
        $('#feedbackForm [name=id]').val(data.id);
    });

    AdminCRUD.bindForm({
        formSelector: '#feedbackForm',
        modalSelector: '#feedbackModal',
        buildUrl: (id) => `{{ url('admin/feedback') }}/${id}`,
        buildMethod: () => 'PUT',
    });

    AdminCRUD.bindDelete('.btn-delete', (id) => `{{ url('admin/feedback') }}/${id}`);
});
</script>
@endpush
