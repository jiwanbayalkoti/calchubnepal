@extends('layouts.admin')

@section('title', 'Banner Sizes')
@section('page-title', 'Banner Sizes')

@push('breadcrumbs')
    <li class="breadcrumb-item active">Banner Sizes</li>
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-end">
            <button type="button" class="btn btn-primary btn-sm" id="btnAddBannerSize">
                <i class="fas fa-plus"></i> Add Banner Size
            </button>
        </div>
        <div class="card-body">
            <table id="bannerSizesTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Size</th>
                        <th>Width</th>
                        <th>Height</th>
                        <th>Sort</th>
                        <th>Active</th>
                        <th style="width:140px;">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="bannerSizeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="bannerSizeForm" autocomplete="off">
                    <input type="hidden" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title">Banner Size</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name <span class="required-star">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label>Code</label>
                            <input type="text" name="code" class="form-control" placeholder="Auto-generated if empty">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Width</label>
                                <input type="number" name="width" class="form-control" min="1">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Height</label>
                                <input type="number" name="height" class="form-control" min="1">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0" min="0">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="custom-control custom-switch mb-2">
                            <input type="checkbox" class="custom-control-input" id="bs_is_responsive" name="is_responsive">
                            <label class="custom-control-label" for="bs_is_responsive">Responsive</label>
                        </div>
                        <div class="custom-control custom-switch mb-2">
                            <input type="checkbox" class="custom-control-input" id="bs_is_custom" name="is_custom">
                            <label class="custom-control-label" for="bs_is_custom">Custom</label>
                        </div>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="bs_is_active" name="is_active" checked>
                            <label class="custom-control-label" for="bs_is_active">Active</label>
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
    AdminCRUD.initDataTable('#bannerSizesTable', {
        ajaxUrl: '{{ route("admin.banner-sizes.data") }}',
        order: [[5, 'asc']],
        columns: [
            { data: 'name', name: 'name' },
            { data: 'code', name: 'code' },
            { data: 'size_label', name: 'size_label', orderable: false },
            { data: 'width', name: 'width', defaultContent: '—' },
            { data: 'height', name: 'height', defaultContent: '—' },
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

    $('#btnAddBannerSize').on('click', function (e) {
        e.preventDefault();
        AdminCRUD.openCreate('#bannerSizeModal', '#bannerSizeForm', 'Add Banner Size');
    });

    AdminCRUD.bindForm({
        formSelector: '#bannerSizeForm',
        modalSelector: '#bannerSizeModal',
        buildUrl: (id) => id ? `{{ url('admin/banner-sizes') }}/${id}` : `{{ route('admin.banner-sizes.store') }}`,
        buildMethod: (id) => id ? 'PUT' : 'POST',
    });

    AdminCRUD.bindEdit('.btn-edit', (id) => `{{ url('admin/banner-sizes') }}/${id}`, '#bannerSizeForm', '#bannerSizeModal', function (data, formSelector) {
        AdminCRUD.autoFill(formSelector, data);
        $('#bannerSizeModal .modal-title').text('Edit Banner Size');
    });

    AdminCRUD.bindDelete('.btn-delete', (id) => `{{ url('admin/banner-sizes') }}/${id}`);
});
</script>
@endpush
