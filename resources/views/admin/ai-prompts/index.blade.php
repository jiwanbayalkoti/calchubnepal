@extends('layouts.admin')

@section('title', 'AI Prompts')
@section('page-title', 'AI Prompts')

@push('breadcrumbs')
    <li class="breadcrumb-item active">AI Prompts</li>
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-end">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#promptModal" id="btnAddPrompt">
                <i class="fas fa-plus"></i> Add Prompt
            </button>
        </div>
        <div class="card-body">
            <table id="promptsTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Purpose</th>
                        <th>Provider</th>
                        <th>Model</th>
                        <th>Active</th>
                        <th>Logs</th>
                        <th style="width:140px;">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="promptModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="promptForm" autocomplete="off">
                    <input type="hidden" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title">AI Prompt</h5>
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
                                <label>Purpose <span class="required-star">*</span></label>
                                <input type="text" name="purpose" class="form-control" required placeholder="e.g. blog_generation">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Prompt Template <span class="required-star">*</span></label>
                            <textarea name="prompt_template" class="form-control" rows="6" required placeholder="Write a blog post about @{{title}}..."></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label>Provider</label>
                                <select name="provider" class="form-control">
                                    <option value="">Default</option>
                                    <option value="openai">OpenAI</option>
                                    <option value="gemini">Gemini</option>
                                    <option value="claude">Claude</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Model</label>
                                <input type="text" name="model" class="form-control">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Temperature</label>
                                <input type="number" step="0.1" min="0" max="2" name="temperature" class="form-control" value="0.7">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Max Tokens</label>
                                <input type="number" min="1" name="max_tokens" class="form-control" placeholder="1024">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="prompt_is_active" name="is_active" checked>
                            <label class="custom-control-label" for="prompt_is_active">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Prompt</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    AdminCRUD.initDataTable('#promptsTable', {
        ajaxUrl: '{{ route("admin.ai-prompts.data") }}',
        order: [[0, 'asc']],
        columns: [
            { data: 'name', name: 'name' },
            { data: 'purpose', name: 'purpose' },
            { data: 'provider', name: 'provider', defaultContent: 'default' },
            { data: 'model', name: 'model', defaultContent: '-' },
            {
                data: 'is_active', name: 'is_active', orderable: false,
                render: (v) => v ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Inactive</span>',
            },
            { data: 'logs_count', name: 'logs_count', orderable: false },
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

    $('#btnAddPrompt').on('click', function (e) {
        e.preventDefault();
        AdminCRUD.openCreate('#promptModal', '#promptForm', 'Add AI Prompt');
    });

    AdminCRUD.bindForm({
        formSelector: '#promptForm',
        modalSelector: '#promptModal',
        buildUrl: (id) => id ? `{{ url('admin/ai-prompts') }}/${id}` : `{{ route('admin.ai-prompts.store') }}`,
        buildMethod: (id) => id ? 'PUT' : 'POST',
    });

    AdminCRUD.bindEdit('.btn-edit', (id) => `{{ url('admin/ai-prompts') }}/${id}`, '#promptForm', '#promptModal', function (data, formSelector) {
        AdminCRUD.autoFill(formSelector, data);
        $('#promptModal .modal-title').text('Edit AI Prompt');
    });

    AdminCRUD.bindDelete('.btn-delete', (id) => `{{ url('admin/ai-prompts') }}/${id}`);
});
</script>
@endpush
