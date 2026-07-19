@extends('layouts.admin')

@section('title', 'Contact Messages')
@section('page-title', 'Contact Messages')

@push('breadcrumbs')
    <li class="breadcrumb-item active">Contact Messages</li>
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-end">
            <select id="filterStatus" class="form-control form-control-sm" style="width:180px;">
                <option value="">All Status</option>
                <option value="new">New</option>
                <option value="read">Read</option>
                <option value="replied">Replied</option>
                <option value="archived">Archived</option>
            </select>
        </div>
        <div class="card-body">
            <table id="messagesTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th style="width:200px;">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="messageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="messageForm" autocomplete="off">
                    <input type="hidden" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title">Message Detail</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <dl class="row">
                            <dt class="col-3">Name</dt><dd class="col-9" id="msg_name"></dd>
                            <dt class="col-3">Email</dt><dd class="col-9" id="msg_email"></dd>
                            <dt class="col-3">Subject</dt><dd class="col-9" id="msg_subject"></dd>
                        </dl>
                        <p id="msg_body" class="border rounded p-2 bg-light"></p>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control" required>
                                <option value="new">New</option>
                                <option value="read">Read</option>
                                <option value="replied">Replied</option>
                                <option value="archived">Archived</option>
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
    AdminCRUD.initDataTable('#messagesTable', {
        ajaxUrl: '{{ route("admin.contact-messages.data") }}',
        order: [[4, 'desc']],
        extraFilters: () => ({ status: $('#filterStatus').val() }),
        columns: [
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'subject', name: 'subject' },
            {
                data: 'status', name: 'status',
                render: (v) => ({ new: 'warning', read: 'info', replied: 'success', archived: 'secondary' }[v] ? `<span class="badge badge-${({new:'warning',read:'info',replied:'success',archived:'secondary'})[v]}">${v}</span>` : v),
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

    AdminCRUD.bindEdit('.btn-view', (id) => `{{ url('admin/contact-messages') }}/${id}`, '#messageForm', '#messageModal', function (data) {
        $('#msg_name').text(data.name);
        $('#msg_email').text(data.email);
        $('#msg_subject').text(data.subject);
        $('#msg_body').text(data.message);
        $('#messageForm [name=status]').val(data.status);
        $('#messageForm [name=id]').val(data.id);
        AdminCRUD.reload();
    });

    AdminCRUD.bindForm({
        formSelector: '#messageForm',
        modalSelector: '#messageModal',
        buildUrl: (id) => `{{ url('admin/contact-messages') }}/${id}`,
        buildMethod: () => 'PUT',
    });

    AdminCRUD.bindDelete('.btn-delete', (id) => `{{ url('admin/contact-messages') }}/${id}`);
});
</script>
@endpush
