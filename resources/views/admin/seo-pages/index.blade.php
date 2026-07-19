@extends('layouts.admin')

@section('title', 'SEO Pages')
@section('page-title', 'SEO Pages')

@push('breadcrumbs')
    <li class="breadcrumb-item active">SEO Pages</li>
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-end">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#seoPageModal" id="btnAddSeoPage">
                <i class="fas fa-plus"></i> Add SEO Page
            </button>
        </div>
        <div class="card-body">
            <table id="seoPagesTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Active</th>
                        <th>Created</th>
                        <th style="width:140px;">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="seoPageModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="seoPageForm" autocomplete="off">
                    <input type="hidden" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title">SEO Page</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8 form-group">
                                <label>Title <span class="required-star">*</span></label>
                                <input type="text" name="title" class="form-control" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Slug</label>
                                <input type="text" name="slug" class="form-control" placeholder="Auto-generated if empty">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Content</label>
                            <textarea name="content" class="form-control" rows="5"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                        <hr>
                        <h6 class="text-muted">SEO</h6>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Meta Title</label>
                                <input type="text" name="meta_title" class="form-control">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Meta Keywords</label>
                                <input type="text" name="meta_keywords" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Meta Description</label>
                            <textarea name="meta_description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Canonical URL</label>
                                <input type="text" name="canonical_url" class="form-control">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Robots</label>
                                <input type="text" name="robots" class="form-control" value="index,follow">
                            </div>
                        </div>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="seo_is_active" name="is_active" checked>
                            <label class="custom-control-label" for="seo_is_active">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Page</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    AdminCRUD.initDataTable('#seoPagesTable', {
        ajaxUrl: '{{ route("admin.seo-pages.data") }}',
        order: [[3, 'desc']],
        columns: [
            { data: 'title', name: 'title' },
            { data: 'slug', name: 'slug' },
            {
                data: 'is_active', name: 'is_active', orderable: false,
                render: (v) => v ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Inactive</span>',
            },
            { data: 'created_at', name: 'created_at' },
            {
                data: null, orderable: false, searchable: false,
                render: (row) => `
                    <div class="table-actions">
                        <button class="btn btn-sm btn-info btn-edit" data-id="${row.id}"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-danger btn-delete" data-id="${row.id}" data-name="${row.title}"><i class="fas fa-trash"></i></button>
                    </div>`,
            },
        ],
    });

    $('#btnAddSeoPage').on('click', function (e) {
        e.preventDefault();
        AdminCRUD.openCreate('#seoPageModal', '#seoPageForm', 'Add SEO Page');
    });

    AdminCRUD.bindForm({
        formSelector: '#seoPageForm',
        modalSelector: '#seoPageModal',
        buildUrl: (id) => id ? `{{ url('admin/seo-pages') }}/${id}` : `{{ route('admin.seo-pages.store') }}`,
        buildMethod: (id) => id ? 'PUT' : 'POST',
    });

    AdminCRUD.bindEdit('.btn-edit', (id) => `{{ url('admin/seo-pages') }}/${id}`, '#seoPageForm', '#seoPageModal', function (data, formSelector) {
        AdminCRUD.autoFill(formSelector, data);
        $('#seoPageModal .modal-title').text('Edit SEO Page');
    });

    AdminCRUD.bindDelete('.btn-delete', (id) => `{{ url('admin/seo-pages') }}/${id}`);
});
</script>
@endpush
