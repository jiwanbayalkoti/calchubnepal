@extends('layouts.admin')

@section('title', 'Advertisers')
@section('page-title', 'Advertisers')

@push('breadcrumbs')
    <li class="breadcrumb-item active">Advertisers</li>
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-end">
            @if(auth()->user()?->hasRole('super-admin'))
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#advertiserModal" id="btnAddAdvertiser">
                    <i class="fas fa-plus"></i> Add Advertiser
                </button>
            @endif
        </div>
        <div class="card-body">
            <table id="advertisersTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Ads</th>
                        <th>Joined</th>
                        <th style="width:140px;">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="advertiserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="advertiserForm" enctype="multipart/form-data" autocomplete="off">
                    <input type="hidden" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title">Advertiser</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Company Name <span class="required-star">*</span></label>
                            <input type="text" name="company_name" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label>Contact Person <span class="required-star">*</span></label>
                            <input type="text" name="contact_person" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label>Email <span class="required-star">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label>Password <span id="advPwdHint" class="required-star">*</span></label>
                            <input type="password" name="password" class="form-control" autocomplete="new-password">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label>Logo</label>
                            <input type="file" name="logo" class="form-control-file" accept="image/*">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group mb-0">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    AdminCRUD.initDataTable('#advertisersTable', {
        ajaxUrl: '{{ route("admin.advertisers.data") }}',
        order: [[0, 'asc']],
        columns: [
            { data: 'company_name', name: 'company_name' },
            { data: 'contact_person', name: 'contact_person' },
            { data: 'email', name: 'email', orderable: false },
            { data: 'phone', name: 'phone', defaultContent: '—' },
            { data: 'status', name: 'status', render: (v) => `<span class="badge badge-info">${v}</span>` },
            { data: 'ads_count', name: 'ads_count', orderable: false },
            { data: 'created_at', name: 'created_at' },
            {
                data: null, orderable: false, searchable: false,
                render: (row) => `
                    <div class="table-actions">
                        <button class="btn btn-sm btn-info btn-edit" data-id="${row.id}"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-danger btn-delete" data-id="${row.id}" data-name="${row.company_name}"><i class="fas fa-trash"></i></button>
                    </div>`
            },
        ],
    });

    $('#btnAddAdvertiser').on('click', function (e) {
        e.preventDefault();
        AdminCRUD.openCreate('#advertiserModal', '#advertiserForm', 'Add Advertiser');
        $('#advPwdHint').addClass('required-star').text('*');
        $('input[name="email"]').prop('disabled', false).prop('required', true);
        $('input[name="password"]').prop('required', true);
    });

    AdminCRUD.bindForm({
        formSelector: '#advertiserForm',
        modalSelector: '#advertiserModal',
        buildUrl: (id) => id ? `{{ url('admin/advertisers') }}/${id}` : `{{ route('admin.advertisers.store') }}`,
        buildMethod: (id) => id ? 'PUT' : 'POST',
    });

    AdminCRUD.bindEdit('.btn-edit', (id) => `{{ url('admin/advertisers') }}/${id}`, '#advertiserForm', '#advertiserModal', function (data, formSelector) {
        AdminCRUD.autoFill(formSelector, data);
        $(formSelector).find('input[name="id"]').val(data.id);
        $('#advertiserModal .modal-title').text('Edit Advertiser');
        $('#advPwdHint').removeClass('required-star').text('(leave blank to keep)');
        $('input[name="email"]').val(data.email).prop('disabled', true).prop('required', false);
        $('input[name="password"]').prop('required', false).val('');
    });

    AdminCRUD.bindDelete('.btn-delete', (id) => `{{ url('admin/advertisers') }}/${id}`);
});
</script>
@endpush
