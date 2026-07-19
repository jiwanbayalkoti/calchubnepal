@extends('layouts.admin')

@section('title', 'Users')
@section('page-title', 'Users')

@push('breadcrumbs')
    <li class="breadcrumb-item active">Users</li>
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <select id="filterRole" class="form-control form-control-sm" style="width:200px;">
                <option value="">All Roles</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#userModal" id="btnAddUser">
                <i class="fas fa-plus"></i> Add User
            </button>
        </div>
        <div class="card-body">
            <table id="usersTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Active</th>
                        <th>Premium</th>
                        <th>Joined</th>
                        <th style="width:140px;">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="userForm" autocomplete="off">
                    <input type="hidden" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title">User</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name <span class="required-star">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label>Email <span class="required-star">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label>Password <span id="pwdHint" class="text-muted">(leave blank to keep current)</span></label>
                            <input type="password" name="password" class="form-control" autocomplete="new-password">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select name="role_id" class="form-control select2">
                                <option value="">No role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="user_is_active" name="is_active" checked>
                                    <label class="custom-control-label" for="user_is_active">Active</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="user_is_premium" name="is_premium">
                                    <label class="custom-control-label" for="user_is_premium">Premium</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mt-3 mb-0">
                            <label>Premium expires at</label>
                            <input type="date" name="premium_expires_at" class="form-control">
                            <small class="form-text text-muted">Leave empty for lifetime manual premium. Cleared automatically when a paid subscription ends.</small>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    AdminCRUD.initDataTable('#usersTable', {
        ajaxUrl: '{{ route("admin.users.data") }}',
        order: [[5, 'desc']],
        extraFilters: () => ({ role_id: $('#filterRole').val() }),
        columns: [
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'role', name: 'role_id', orderable: false },
            {
                data: 'is_active', name: 'is_active', orderable: false,
                render: (v, t, row) => `<button class="btn btn-sm btn-toggle-active ${v ? 'btn-success' : 'btn-outline-secondary'}" data-id="${row.id}">${v ? 'Active' : 'Inactive'}</button>`,
            },
            {
                data: 'is_premium', name: 'is_premium', orderable: false,
                render: (v) => v ? '<span class="badge badge-warning">Premium</span>' : '<span class="badge badge-light">Free</span>',
            },
            { data: 'created_at', name: 'created_at' },
            {
                data: null, orderable: false, searchable: false,
                render: (row) => `
                    <div class="table-actions">
                        <button class="btn btn-sm btn-info btn-edit" data-id="${row.id}"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-danger btn-delete" data-id="${row.id}" data-name="${row.name}"><i class="fas fa-trash"></i></button>
                    </div>`,
            },
        ],
    });

    $('#filterRole').on('change', () => AdminCRUD.reload());

    $('#btnAddUser').on('click', function (e) {
        e.preventDefault();
        AdminCRUD.openCreate('#userModal', '#userForm', 'Add User');
        $('[name=password]').prop('required', true);
        $('#pwdHint').hide();
    });

    AdminCRUD.bindForm({
        formSelector: '#userForm',
        modalSelector: '#userModal',
        buildUrl: (id) => id ? `{{ url('admin/users') }}/${id}` : `{{ route('admin.users.store') }}`,
        buildMethod: (id) => id ? 'PUT' : 'POST',
    });

    AdminCRUD.bindEdit('.btn-edit', (id) => `{{ url('admin/users') }}/${id}`, '#userForm', '#userModal', function (data, formSelector) {
        AdminCRUD.autoFill(formSelector, data);
        $('[name=password]').prop('required', false).val('');
        $('#pwdHint').show();
        $('#userModal .modal-title').text('Edit User');
    });

    AdminCRUD.bindDelete('.btn-delete', (id) => `{{ url('admin/users') }}/${id}`);
    AdminCRUD.bindToggle('.btn-toggle-active', (id) => `{{ url('admin/users') }}/${id}/toggle-active`);
});
</script>
@endpush
