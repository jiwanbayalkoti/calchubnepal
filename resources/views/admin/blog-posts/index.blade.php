@extends('layouts.admin')

@section('title', 'Blog Posts')
@section('page-title', 'Blog Posts')

@push('breadcrumbs')
    <li class="breadcrumb-item active">Blog Posts</li>
@endpush

@push('styles')
{{-- CKEditor 5 Classic — free build, no API key --}}
<style>
    .ck-editor__editable_inline { min-height: 280px; }
    .ck.ck-editor { width: 100%; }
</style>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
@endpush

@section('content')
    <div class="card mb-3 border-primary">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <span><i class="fas fa-robot"></i> AI Blog Writer</span>
            <small class="opacity-75">Admin instruction → review → save / publish</small>
        </div>
        <div class="card-body">
            <form id="aiBlogForm" autocomplete="off">
                <div class="form-group">
                    <label>Instructions for AI <span class="required-star">*</span></label>
                    <textarea name="instructions" id="aiInstructions" class="form-control" rows="4" required
                        placeholder="Example: Write a practical guide on how to generate a WiFi QR code for cafes in Nepal. Include steps, common mistakes, and a CTA to CalchubNepal QR generator. Keep language simple."></textarea>
                    <div class="invalid-feedback" data-error-for="instructions"></div>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Title hint (optional)</label>
                        <input type="text" name="title_hint" class="form-control" placeholder="Suggested title direction">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Primary keyword</label>
                        <input type="text" name="keyword" class="form-control" placeholder="wifi qr code nepal">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Category</label>
                        <select name="category_id" class="form-control">
                            <option value="">Auto / General</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>Language</label>
                        <select name="language" class="form-control">
                            <option value="en">English</option>
                            <option value="ne">Nepali</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Tone</label>
                        <select name="tone" class="form-control">
                            <option value="clear, helpful, professional">Professional</option>
                            <option value="friendly and conversational">Friendly</option>
                            <option value="practical how-to">How-to / Practical</option>
                            <option value="SEO-focused educational">SEO Educational</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Word count</label>
                        <select name="word_count" class="form-control">
                            <option value="700">~700</option>
                            <option value="900" selected>~900</option>
                            <option value="1200">~1200</option>
                            <option value="1500">~1500</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Related tool name</label>
                        <input type="text" name="calculator_title" class="form-control" placeholder="QR Code Generator">
                    </div>
                </div>
                <div class="row align-items-end">
                    <div class="col-md-4 form-group mb-md-0">
                        <label>After generate</label>
                        <select name="save_mode" id="aiSaveMode" class="form-control">
                            <option value="fill">Fill editor (review first)</option>
                            <option value="draft">Save as Draft</option>
                            <option value="published">Publish now</option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group mb-md-0" id="aiPublishAtWrap" style="display:none;">
                        <label>Publish date (optional)</label>
                        <input type="datetime-local" name="published_at" class="form-control">
                        <small class="text-muted">Empty = publish immediately</small>
                    </div>
                    <div class="col-md-4 form-group mb-0 text-md-right">
                        <button type="submit" class="btn btn-primary" id="btnGenerateAi">
                            <i class="fas fa-magic"></i> Generate with AI
                        </button>
                    </div>
                </div>
            </form>
            <div id="aiBlogStatus" class="mt-3 small text-muted" style="display:none;"></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <select id="filterStatus" class="form-control form-control-sm" style="width:180px;">
                <option value="">All Status</option>
                <option value="draft">Draft</option>
                <option value="published">Published</option>
                <option value="archived">Archived</option>
            </select>
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#postModal" id="btnAddPost">
                <i class="fas fa-plus"></i> Add Post
            </button>
        </div>
        <div class="card-body">
            <table id="postsTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Published</th>
                        <th style="width:140px;">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="postModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="max-height: calc(100vh - 3.5rem); display: flex; flex-direction: column;">
                <form id="postForm" autocomplete="off" style="display: flex; flex-direction: column; min-height: 0; flex: 1;">
                    <input type="hidden" name="id">
                    <input type="hidden" name="ai_generated" id="postAiGenerated" value="0">
                    <div class="modal-header flex-shrink-0">
                        <h5 class="modal-title">Blog Post</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body" style="overflow-y: auto; flex: 1 1 auto; max-height: calc(100vh - 12rem);">
                        <div class="row">
                            <div class="col-md-8 form-group">
                                <label>Title <span class="required-star">*</span></label>
                                <input type="text" name="title" class="form-control" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Category</label>
                                <select name="blog_category_id" class="form-control select2">
                                    <option value="">Uncategorized</option>
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
                            <div class="col-md-3 form-group">
                                <label>Status <span class="required-star">*</span></label>
                                <select name="status" class="form-control" required>
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Publish Date</label>
                                <input type="datetime-local" name="published_at" class="form-control">
                                <small class="form-text text-muted">Nepal time (Asia/Kathmandu). Leave empty to publish immediately.</small>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Excerpt</label>
                            <textarea name="excerpt" class="form-control" rows="2"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label>Content <span class="required-star">*</span></label>
                            <textarea name="content" id="postContent" class="form-control" rows="8" required></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label>Tags (comma separated)</label>
                            <input type="text" name="tags" class="form-control" placeholder="finance, tips, howto">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Featured Image URL</label>
                                <input type="text" name="featured_image" class="form-control">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 form-group d-flex align-items-end">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="post_is_featured" name="is_featured">
                                    <label class="custom-control-label" for="post_is_featured">Featured Post</label>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <h6 class="text-muted">SEO</h6>
                        <div class="form-group">
                            <label>Meta Title</label>
                            <input type="text" name="meta_title" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Meta Keywords</label>
                            <input type="text" name="meta_keywords" class="form-control" placeholder="keyword1, keyword2">
                        </div>
                        <div class="form-group mb-0">
                            <label>Meta Description</label>
                            <textarea name="meta_description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer flex-shrink-0">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Post</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    let contentEditor = null;

    function syncEditorToTextarea() {
        if (contentEditor) {
            contentEditor.updateSourceElement();
        }
    }

    function setEditorData(html) {
        if (contentEditor) {
            contentEditor.setData(html || '');
        } else {
            $('#postContent').val(html || '');
        }
    }

    function initEditor() {
        if (typeof ClassicEditor === 'undefined') {
            console.error('CKEditor 5 failed to load');
            return;
        }
        if (contentEditor) {
            return;
        }
        ClassicEditor
            .create(document.querySelector('#postContent'), {
                toolbar: [
                    'heading', '|',
                    'bold', 'italic', 'link', '|',
                    'bulletedList', 'numberedList', 'blockQuote', '|',
                    'insertTable', '|',
                    'undo', 'redo',
                ],
            })
            .then((editor) => {
                contentEditor = editor;
                editor.model.document.on('change:data', () => editor.updateSourceElement());
            })
            .catch((err) => console.error('CKEditor init error', err));
    }

    initEditor();

    $('#postForm').on('submit', function () {
        syncEditorToTextarea();
    });

    $('#aiSaveMode').on('change', function () {
        $('#aiPublishAtWrap').toggle($(this).val() === 'published');
    });

    function clearAiErrors() {
        $('#aiBlogForm .is-invalid').removeClass('is-invalid');
        $('#aiBlogForm .invalid-feedback').text('');
    }

    function fillPostFromAi(data) {
        const $form = $('#postForm');
        $form.find('[name="id"]').val('');
        $form.find('[name="title"]').val(data.title || '');
        $form.find('[name="slug"]').val(data.slug || '');
        $form.find('[name="excerpt"]').val(data.excerpt || '');
        $form.find('[name="meta_title"]').val(data.meta_title || '');
        $form.find('[name="meta_description"]').val(data.meta_description || '');
        $form.find('[name="meta_keywords"]').val(data.meta_keywords || '');
        $form.find('[name="status"]').val('draft');
        $form.find('[name="blog_category_id"]').val(data.blog_category_id || '').trigger('change');
        const tags = Array.isArray(data.tags) ? data.tags.join(', ') : (data.tags || '');
        $form.find('[name="tags"]').val(tags);
        $('#postAiGenerated').val('1');
        setEditorData(data.content || '');
        $('#postModal .modal-title').text('AI Blog — Review & Save');
        $('#postModal').modal('show');
    }

    $('#aiBlogForm').on('submit', function (e) {
        e.preventDefault();
        clearAiErrors();

        const $btn = $('#btnGenerateAi');
        const original = $btn.html();
        const saveMode = $('#aiSaveMode').val() || 'fill';
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generating…');
        $('#aiBlogStatus').show().removeClass('text-danger text-success').addClass('text-muted')
            .text('Calling AI… this can take 15–40 seconds.');

        $.ajax({
            url: '{{ route("admin.blog-posts.generate-ai") }}',
            method: 'POST',
            data: $(this).serialize(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        }).done(function (res) {
            toastr.success(res.message || 'AI blog ready.');
            $('#aiBlogStatus').removeClass('text-muted text-danger').addClass('text-success')
                .text(res.message || 'Done.');

            if (saveMode === 'fill' && res.data) {
                fillPostFromAi(res.data);
            } else {
                AdminCRUD.reload();
            }
        }).fail(function (xhr) {
            const json = xhr.responseJSON || {};
            let msg = json.message || 'AI generation failed.';
            if (json.errors) {
                Object.keys(json.errors).forEach((key) => {
                    const $field = $('#aiBlogForm [name="' + key + '"]');
                    $field.addClass('is-invalid');
                    $field.siblings('.invalid-feedback').text(json.errors[key][0]);
                    $('#aiBlogForm [data-error-for="' + key + '"]').text(json.errors[key][0]);
                });
                msg = Object.values(json.errors)[0][0] || msg;
            }
            toastr.error(msg);
            $('#aiBlogStatus').removeClass('text-muted text-success').addClass('text-danger').text(msg);
        }).always(function () {
            $btn.prop('disabled', false).html(original);
        });
    });

    AdminCRUD.initDataTable('#postsTable', {
        ajaxUrl: '{{ route("admin.blog-posts.data") }}',
        order: [[5, 'desc']],
        extraFilters: () => ({ status: $('#filterStatus').val() }),
        columns: [
            { data: 'title', name: 'title' },
            { data: 'category', name: 'blog_category_id', orderable: false },
            { data: 'author', name: 'user_id', orderable: false },
            {
                data: 'status', name: 'status',
                render: (v) => ({ draft: 'secondary', published: 'success', archived: 'dark' }[v] ? `<span class="badge badge-${({draft:'secondary',published:'success',archived:'dark'})[v]}">${v}</span>` : v),
            },
            { data: 'views_count', name: 'views_count' },
            { data: 'published_at', name: 'published_at' },
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

    $('#filterStatus').on('change', () => AdminCRUD.reload());

    $('#btnAddPost').on('click', function (e) {
        e.preventDefault();
        AdminCRUD.openCreate('#postModal', '#postForm', 'Add Blog Post');
        $('#postAiGenerated').val('0');
        setEditorData('');
    });

    AdminCRUD.bindForm({
        formSelector: '#postForm',
        modalSelector: '#postModal',
        buildUrl: (id) => id ? `{{ url('admin/blog-posts') }}/${id}` : `{{ route('admin.blog-posts.store') }}`,
        buildMethod: (id) => id ? 'PUT' : 'POST',
    });

    AdminCRUD.bindEdit('.btn-edit', (id) => `{{ url('admin/blog-posts') }}/${id}`, '#postForm', '#postModal', function (data, formSelector) {
        AdminCRUD.autoFill(formSelector, data);
        setEditorData(data.content || '');
        $('#postAiGenerated').val(data.ai_generated ? '1' : '0');
        $('#postModal .modal-title').text('Edit Blog Post');
    });

    AdminCRUD.bindDelete('.btn-delete', (id) => `{{ url('admin/blog-posts') }}/${id}`);
});
</script>
@endpush
