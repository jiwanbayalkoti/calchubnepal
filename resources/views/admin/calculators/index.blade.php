@extends('layouts.admin')

@section('title', 'Calculators')
@section('page-title', 'Calculators')

@push('breadcrumbs')
    <li class="breadcrumb-item active">Calculators</li>
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex gap-2">
                <select id="filterCategory" class="form-control form-control-sm" style="width:200px;">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                <select id="filterStatus" class="form-control form-control-sm ml-2" style="width:150px;">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <select id="filterPremium" class="form-control form-control-sm ml-2" style="width:150px;">
                    <option value="">All Plans</option>
                    <option value="free">Free</option>
                    <option value="premium">Premium</option>
                </select>
            </div>
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#calculatorModal" id="btnAddCalculator">
                <i class="fas fa-plus"></i> Add Calculator
            </button>
        </div>
        <div class="card-body">
            <table id="calculatorsTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Premium</th>
                        <th>Featured</th>
                        <th>Active</th>
                        <th>Usage</th>
                        <th>Created</th>
                        <th style="width:160px;">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- Create / Edit Modal --}}
    <div class="modal fade" id="calculatorModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="calculatorForm" autocomplete="off">
                    <input type="hidden" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title">Calculator</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body position-relative">
                        <div class="row">
                            <div class="col-md-8 form-group">
                                <label>Title <span class="required-star">*</span></label>
                                <input type="text" name="title" class="form-control" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Category <span class="required-star">*</span></label>
                                <select name="calculator_category_id" class="form-control select2" required>
                                    <option value="">Select category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Slug</label>
                                <input type="text" name="slug" class="form-control" placeholder="Auto-generated if empty">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Formula Key <span class="required-star">*</span></label>
                                <input type="text" name="formula_key" class="form-control" required placeholder="e.g. bmi_calculator">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Short Description</label>
                            <textarea name="short_description" class="form-control" rows="2"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="form-group">
                            <label>Full Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="form-group">
                            <label>Input Schema (JSON) <span class="required-star">*</span></label>
                            <textarea name="input_schema" class="form-control" rows="4" required placeholder='[{"name":"weight","type":"number","label":"Weight"}]'></textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Validation Rules (JSON)</label>
                                <textarea name="validation_rules" class="form-control" rows="3"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Result Schema (JSON)</label>
                                <textarea name="result_schema" class="form-control" rows="3"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <hr>
                        <h6 class="text-muted">SEO</h6>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Meta Title</label>
                                <input type="text" name="meta_title" class="form-control">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Meta Keywords</label>
                                <input type="text" name="meta_keywords" class="form-control">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Meta Description</label>
                            <textarea name="meta_description" class="form-control" rows="2"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="is_premium" name="is_premium">
                                    <label class="custom-control-label" for="is_premium">Premium</label>
                                </div>
                            </div>
                            <div class="col-md-4 form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured">
                                    <label class="custom-control-label" for="is_featured">Featured</label>
                                </div>
                            </div>
                            <div class="col-md-4 form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" checked>
                                    <label class="custom-control-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Calculator</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    const columns = [
        { data: 'title', name: 'title' },
        { data: 'category', name: 'calculator_category_id', orderable: false },
        {
            data: 'is_premium', name: 'is_premium', orderable: false,
            render: (v) => v ? '<span class="badge badge-warning">Premium</span>' : '<span class="badge badge-light">Free</span>',
        },
        {
            data: 'is_featured', name: 'is_featured', orderable: false,
            render: (v, t, row) => `<button class="btn btn-sm btn-toggle-featured ${v ? 'btn-success' : 'btn-outline-secondary'}" data-id="${row.id}"><i class="fas fa-star"></i></button>`,
        },
        {
            data: 'is_active', name: 'is_active', orderable: false,
            render: (v, t, row) => `<button class="btn btn-sm btn-toggle-active ${v ? 'btn-success' : 'btn-outline-secondary'}" data-id="${row.id}">${v ? 'Active' : 'Inactive'}</button>`,
        },
        { data: 'usage_count', name: 'usage_count' },
        { data: 'created_at', name: 'created_at' },
        {
            data: null, orderable: false, searchable: false,
            render: (row) => `
                <div class="table-actions">
                    <button class="btn btn-sm btn-info btn-edit" data-id="${row.id}"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger btn-delete" data-id="${row.id}" data-name="${row.title}"><i class="fas fa-trash"></i></button>
                </div>`,
        },
    ];

    AdminCRUD.initDataTable('#calculatorsTable', {
        ajaxUrl: '{{ route("admin.calculators.data") }}',
        columns,
        order: [[6, 'desc']],
        extraFilters: () => ({
            category_id: $('#filterCategory').val(),
            status: $('#filterStatus').val(),
            premium: $('#filterPremium').val(),
        }),
    });

    $('#filterCategory, #filterStatus, #filterPremium').on('change', () => AdminCRUD.reload());

    $('#btnAddCalculator').on('click', function (e) {
        e.preventDefault();
        AdminCRUD.openCreate('#calculatorModal', '#calculatorForm', 'Add Calculator');
    });

    AdminCRUD.bindForm({
        formSelector: '#calculatorForm',
        modalSelector: '#calculatorModal',
        buildUrl: (id) => id ? `{{ url('admin/calculators') }}/${id}` : `{{ route('admin.calculators.store') }}`,
        buildMethod: (id) => id ? 'PUT' : 'POST',
    });

    AdminCRUD.bindEdit('.btn-edit', (id) => `{{ url('admin/calculators') }}/${id}`, '#calculatorForm', '#calculatorModal', function (data, formSelector) {
        AdminCRUD.autoFill(formSelector, data);
        $('#calculatorModal .modal-title').text('Edit Calculator');
    });

    AdminCRUD.bindDelete('.btn-delete', (id) => `{{ url('admin/calculators') }}/${id}`, {
        text: 'This will permanently delete the calculator.',
    });

    AdminCRUD.bindToggle('.btn-toggle-active', (id) => `{{ url('admin/calculators') }}/${id}/toggle-active`);
    AdminCRUD.bindToggle('.btn-toggle-featured', (id) => `{{ url('admin/calculators') }}/${id}/toggle-featured`);
});
</script>
@endpush
