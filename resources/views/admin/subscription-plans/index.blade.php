@extends('layouts.admin')

@section('title', 'Subscription Plans')
@section('page-title', 'Subscription Plans')

@push('breadcrumbs')
    <li class="breadcrumb-item active">Subscription Plans</li>
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-end">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#planModal" id="btnAddPlan">
                <i class="fas fa-plus"></i> Add Plan
            </button>
        </div>
        <div class="card-body">
            <table id="plansTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Billing</th>
                        <th>Active</th>
                        <th>Subscribers</th>
                        <th style="width:140px;">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="planModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="planForm" autocomplete="off">
                    <input type="hidden" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title">Subscription Plan</h5>
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
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>Price <span class="required-star">*</span></label>
                                <input type="number" step="0.01" min="0" name="price" class="form-control" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Currency <span class="required-star">*</span></label>
                                <input type="text" name="currency" class="form-control" value="USD" maxlength="3" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Billing Period <span class="required-star">*</span></label>
                                <select name="billing_period" class="form-control" required>
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                    <option value="lifetime">Lifetime</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label>Features (comma separated)</label>
                            <input type="text" name="features" id="features_text" class="form-control" placeholder="Ad-free experience, AI-powered result explanations, Unlimited PDF exports">
                            <small class="form-text text-muted">Separate each feature with a comma.</small>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>API Rate Limit / min</label>
                                <input type="number" min="0" name="api_rate_limit" class="form-control" value="0">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>PDF Export Limit</label>
                                <input type="number" min="0" name="pdf_limit" class="form-control" value="0">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4 form-group d-flex align-items-end">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="plan_is_active" name="is_active" checked>
                                    <label class="custom-control-label" for="plan_is_active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    AdminCRUD.initDataTable('#plansTable', {
        ajaxUrl: '{{ route("admin.subscription-plans.data") }}',
        order: [[1, 'asc']],
        columns: [
            { data: 'name', name: 'name' },
            { data: 'price', name: 'price', render: (v, t, row) => `${row.currency} ${parseFloat(v).toFixed(2)}` },
            { data: 'billing_period', name: 'billing_period' },
            {
                data: 'is_active', name: 'is_active', orderable: false,
                render: (v) => v ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Inactive</span>',
            },
            { data: 'subscriptions_count', name: 'subscriptions_count', orderable: false },
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

    $('#btnAddPlan').on('click', function (e) {
        e.preventDefault();
        AdminCRUD.openCreate('#planModal', '#planForm', 'Add Plan');
        $('#features_text').val('');
    });

    AdminCRUD.bindForm({
        formSelector: '#planForm',
        modalSelector: '#planModal',
        buildUrl: (id) => id ? `{{ url('admin/subscription-plans') }}/${id}` : `{{ route('admin.subscription-plans.store') }}`,
        buildMethod: (id) => id ? 'PUT' : 'POST',
    });

    AdminCRUD.bindEdit('.btn-edit', (id) => `{{ url('admin/subscription-plans') }}/${id}`, '#planForm', '#planModal', function (data, formSelector) {
        AdminCRUD.autoFill(formSelector, data);
        const features = Array.isArray(data.features) ? data.features.join(', ') : (data.features || '');
        $('#features_text').val(features);
        $('#planModal .modal-title').text('Edit Plan');
    });

    AdminCRUD.bindDelete('.btn-delete', (id) => `{{ url('admin/subscription-plans') }}/${id}`);
});
</script>
@endpush
