@extends('layouts.admin')

@section('title', 'Blog Posts')
@section('page-title', 'Blog Posts')

@push('breadcrumbs')
    <li class="breadcrumb-item active">Blog Posts</li>
@endpush

@push('styles')
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
@endpush

@section('content')
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
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="postForm" autocomplete="off">
                    <input type="hidden" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title">Blog Post</h5>
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
                            <label>Meta Description</label>
                            <textarea name="meta_description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
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
    let tinyReady = false;
    function initEditor() {
        if (typeof tinymce === 'undefined') return;
        tinymce.init({
            selector: '#postContent',
            height: 320,
            menubar: false,
            plugins: 'lists link image code table',
            toolbar: 'undo redo | bold italic | bullist numlist | link image | code',
            setup: (editor) => editor.on('change', () => editor.save()),
        });
        tinyReady = true;
    }
    initEditor();

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
        if (tinyReady && tinymce.get('postContent')) tinymce.get('postContent').setContent('');
    });

    AdminCRUD.bindForm({
        formSelector: '#postForm',
        modalSelector: '#postModal',
        buildUrl: (id) => id ? `{{ url('admin/blog-posts') }}/${id}` : `{{ route('admin.blog-posts.store') }}`,
        buildMethod: (id) => id ? 'PUT' : 'POST',
    });

    AdminCRUD.bindEdit('.btn-edit', (id) => `{{ url('admin/blog-posts') }}/${id}`, '#postForm', '#postModal', function (data, formSelector) {
        AdminCRUD.autoFill(formSelector, data);
        if (tinyReady) tinymce.get('postContent').setContent(data.content || '');
        $('#postModal .modal-title').text('Edit Blog Post');
    });

    AdminCRUD.bindDelete('.btn-delete', (id) => `{{ url('admin/blog-posts') }}/${id}`);
});
</script>
@endpush
