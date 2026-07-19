@extends('layouts.admin')

@section('title', 'Categories')
@section('page-title', 'Calculator Categories')

@push('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.calculators.index') }}">Calculators</a></li>
    <li class="breadcrumb-item active">Categories</li>
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-end">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#categoryModal" id="btnAddCategory">
                <i class="fas fa-plus"></i> Add Category
            </button>
        </div>
        <div class="card-body">
            <table id="categoriesTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Calculators</th>
                        <th>Sort Order</th>
                        <th>Active</th>
                        <th style="width:140px;">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="categoryForm" autocomplete="off">
                    <input type="hidden" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title">Category</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name <span class="required-star">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label>Slug</label>
                            <input type="text" name="slug" class="form-control" placeholder="Auto-generated if empty">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label>Icon (CSS class)</label>
                            <input type="text" name="icon" class="form-control" placeholder="fas fa-calculator">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label>Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0" min="0">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="cat_is_active" name="is_active" checked>
                            <label class="custom-control-label" for="cat_is_active">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    AdminCRUD.initDataTable('#categoriesTable', {
        ajaxUrl: '{{ route("admin.categories.data") }}',
        order: [[3, 'asc']],
        columns: [
            { data: 'name', name: 'name' },
            { data: 'slug', name: 'slug' },
            { data: 'calculators_count', name: 'calculators_count', orderable: false },
            { data: 'sort_order', name: 'sort_order' },
            {
                data: 'is_active', name: 'is_active', orderable: false,
                render: (v) => v ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Inactive</span>',
            },
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

    $('#btnAddCategory').on('click', function (e) {
        e.preventDefault();
        AdminCRUD.openCreate('#categoryModal', '#categoryForm', 'Add Category');
    });

    AdminCRUD.bindForm({
        formSelector: '#categoryForm',
        modalSelector: '#categoryModal',
        buildUrl: (id) => id ? `{{ url('admin/categories') }}/${id}` : `{{ route('admin.categories.store') }}`,
        buildMethod: (id) => id ? 'PUT' : 'POST',
    });

    AdminCRUD.bindEdit('.btn-edit', (id) => `{{ url('admin/categories') }}/${id}`, '#categoryForm', '#categoryModal', function (data, formSelector) {
        AdminCRUD.autoFill(formSelector, data);
        $('#categoryModal .modal-title').text('Edit Category');
    });

    AdminCRUD.bindDelete('.btn-delete', (id) => `{{ url('admin/categories') }}/${id}`);
});
</script>
@endpush
