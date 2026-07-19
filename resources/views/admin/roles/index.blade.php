@extends('layouts.admin')

@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions')

@push('breadcrumbs')
    <li class="breadcrumb-item active">Roles &amp; Permissions</li>
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-end">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#roleModal" id="btnAddRole">
                <i class="fas fa-plus"></i> Add Role
            </button>
        </div>
        <div class="card-body">
            <table id="rolesTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>System</th>
                        <th>Permissions</th>
                        <th>Users</th>
                        <th style="width:140px;">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="roleModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="roleForm" autocomplete="off">
                    <input type="hidden" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title">Role</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Name <span class="required-star">*</span></label>
                                <input type="text" name="name" class="form-control" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Slug</label>
                                <input type="text" name="slug" class="form-control" placeholder="Auto-generated if empty">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                        <label>Permissions</label>
                        <div class="border rounded p-3" style="max-height: 320px; overflow-y: auto;">
                            @foreach ($permissions as $module => $modulePermissions)
                                <div class="mb-2">
                                    <strong class="text-capitalize">{{ str_replace('_', ' ', $module) }}</strong>
                                    <div class="row">
                                        @foreach ($modulePermissions as $permission)
                                            <div class="col-md-4">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input permission-checkbox" name="permissions[]" value="{{ $permission->id }}" id="perm{{ $permission->id }}">
                                                    <label class="custom-control-label" for="perm{{ $permission->id }}">{{ $permission->action }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    AdminCRUD.initDataTable('#rolesTable', {
        ajaxUrl: '{{ route("admin.roles.data") }}',
        order: [[0, 'asc']],
        columns: [
            { data: 'name', name: 'name' },
            { data: 'slug', name: 'slug' },
            {
                data: 'is_system', name: 'is_system', orderable: false,
                render: (v) => v ? '<span class="badge badge-info">System</span>' : '<span class="badge badge-light">Custom</span>',
            },
            { data: 'permissions_count', name: 'permissions_count', orderable: false },
            { data: 'users_count', name: 'users_count', orderable: false },
            {
                data: null, orderable: false, searchable: false,
                render: (row) => `
                    <div class="table-actions">
                        <button class="btn btn-sm btn-info btn-edit" data-id="${row.id}"><i class="fas fa-edit"></i></button>
                        ${row.is_system ? '' : `<button class="btn btn-sm btn-danger btn-delete" data-id="${row.id}" data-name="${row.name}"><i class="fas fa-trash"></i></button>`}
                    </div>`,
            },
        ],
    });

    $('#btnAddRole').on('click', function (e) {
        e.preventDefault();
        AdminCRUD.openCreate('#roleModal', '#roleForm', 'Add Role');
        $('.permission-checkbox').prop('checked', false);
    });

    AdminCRUD.bindForm({
        formSelector: '#roleForm',
        modalSelector: '#roleModal',
        buildUrl: (id) => id ? `{{ url('admin/roles') }}/${id}` : `{{ route('admin.roles.store') }}`,
        buildMethod: (id) => id ? 'PUT' : 'POST',
    });

    AdminCRUD.bindEdit('.btn-edit', (id) => `{{ url('admin/roles') }}/${id}`, '#roleForm', '#roleModal', function (data) {
        $('.permission-checkbox').prop('checked', false);
        $('#roleForm [name=name]').val(data.name);
        $('#roleForm [name=slug]').val(data.slug);
        $('#roleForm [name=description]').val(data.description);
        (data.permissions || []).forEach((id) => $(`#perm${id}`).prop('checked', true));
        $('#roleModal .modal-title').text('Edit Role');
    });

    AdminCRUD.bindDelete('.btn-delete', (id) => `{{ url('admin/roles') }}/${id}`);
});
</script>
@endpush
