@extends('layouts.admin')

@section('title', 'Advertisements')
@section('page-title', 'Advertisements')

@push('breadcrumbs')
    <li class="breadcrumb-item active">Advertisements</li>
@endpush

@section('content')
    <div class="card card-outline card-secondary mb-3">
        <div class="card-header">
            <h3 class="card-title mb-0"><i class="fas fa-ruler-combined mr-1"></i> Ad positions &amp; standard image sizes</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Position</th>
                            <th>Where it shows</th>
                            <th>Recommended size</th>
                            <th>IAB name</th>
                            <th>Also OK</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($adPositions as $key => $meta)
                            <tr>
                                <td><code>{{ $key }}</code><br><strong>{{ $meta['label'] }}</strong></td>
                                <td class="small">{{ $meta['placement'] }}</td>
                                <td>
                                    <span class="badge badge-primary">{{ $meta['size_label'] }}</span>
                                </td>
                                <td class="small">{{ $meta['iab_name'] }}</td>
                                <td class="small text-muted">{{ implode(', ', $meta['alternates'] ?? []) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="small text-muted mb-0 px-3 py-2">
                Upload JPG, PNG, WEBP or GIF — max {{ $maxUploadKb }} KB. Match the recommended size for the selected position so the banner is not stretched or cropped.
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-end">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#adModal" id="btnAddAd">
                <i class="fas fa-plus"></i> Add Advertisement
            </button>
        </div>
        <div class="card-body">
            <table id="adsTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Type</th>
                        <th>Active</th>
                        <th>Impressions</th>
                        <th>Clicks</th>
                        <th style="width:140px;">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="adModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="adForm" autocomplete="off" enctype="multipart/form-data">
                    <input type="hidden" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title">Advertisement</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Name <span class="required-star">*</span></label>
                                <input type="text" name="name" class="form-control" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Position <span class="required-star">*</span></label>
                                <select name="position" id="ad_position" class="form-control" required>
                                    @foreach ($adPositions as $key => $meta)
                                        <option value="{{ $key }}"
                                            data-size="{{ $meta['size_label'] }}"
                                            data-hint="{{ $meta['hint'] }}"
                                            data-placement="{{ $meta['placement'] }}"
                                            data-iab="{{ $meta['iab_name'] }}"
                                            data-alternates="{{ implode(', ', $meta['alternates'] ?? []) }}">
                                            {{ $meta['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Type <span class="required-star">*</span></label>
                                <select name="ad_type" class="form-control" required>
                                    <option value="banner">Banner</option>
                                    <option value="affiliate">Affiliate</option>
                                    <option value="html">HTML</option>
                                    <option value="adsense">AdSense</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div id="adSizeGuide" class="alert alert-info py-2 px-3">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-info-circle mt-1 mr-2"></i>
                                <div>
                                    <strong>Recommended image size: <span id="adSizeLabel">728 × 90 px</span></strong>
                                    <span class="text-muted">(<span id="adIabName">Leaderboard</span>)</span>
                                    <div class="small mb-0" id="adSizeHint">Create a wide horizontal banner.</div>
                                    <div class="small text-muted mb-0">
                                        Placement: <span id="adPlacement">—</span>
                                        <span id="adAlternatesWrap"> · Also OK: <span id="adAlternates"></span></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>HTML / Banner Content</label>
                            <textarea name="content" class="form-control" rows="3"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label>AdSense Code</label>
                            <textarea name="adsense_code" class="form-control" rows="2"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="form-group">
                            <label>Banner image upload</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="ad_image_file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif">
                                <label class="custom-file-label" for="ad_image_file">Choose image…</label>
                            </div>
                            <small class="form-text text-muted">
                                Upload at the recommended size above. Max {{ $maxUploadKb }} KB. JPG / PNG / WEBP / GIF.
                            </small>
                            <div class="invalid-feedback d-block" id="image_file-error"></div>
                        </div>

                        <div id="adImagePreviewWrap" class="form-group d-none">
                            <label>Current image</label>
                            <div class="border rounded p-2 bg-light d-inline-block">
                                <img id="adImagePreview" src="" alt="Ad preview" style="max-width:100%;max-height:140px;display:block;">
                            </div>
                            <div class="custom-control custom-checkbox mt-2">
                                <input type="checkbox" class="custom-control-input" id="ad_remove_image" name="remove_image" value="1">
                                <label class="custom-control-label" for="ad_remove_image">Remove current image</label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Or external image URL</label>
                                <input type="text" name="image" class="form-control" placeholder="https://… (optional if you upload a file)">
                                <small class="form-text text-muted">Leave blank when uploading a file. Used only for remote image URLs.</small>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Link URL</label>
                                <input type="text" name="link_url" class="form-control" placeholder="https://…">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>Start Date</label>
                                <input type="date" name="start_at" class="form-control">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>End Date</label>
                                <input type="date" name="end_at" class="form-control">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4 form-group d-flex align-items-end">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="ad_is_active" name="is_active" checked>
                                    <label class="custom-control-label" for="ad_is_active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Advertisement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    const $position = $('#ad_position');
    const $fileInput = $('#ad_image_file');
    const $fileLabel = $fileInput.next('.custom-file-label');
    const $previewWrap = $('#adImagePreviewWrap');
    const $preview = $('#adImagePreview');
    const $removeImage = $('#ad_remove_image');

    function updateSizeGuide() {
        const $opt = $position.find('option:selected');
        $('#adSizeLabel').text($opt.data('size') || '—');
        $('#adIabName').text($opt.data('iab') || '—');
        $('#adSizeHint').text($opt.data('hint') || '');
        $('#adPlacement').text($opt.data('placement') || '—');
        const alts = $opt.data('alternates') || '';
        if (alts) {
            $('#adAlternates').text(alts);
            $('#adAlternatesWrap').show();
        } else {
            $('#adAlternatesWrap').hide();
        }
    }

    function resetImageUi() {
        $fileInput.val('');
        $fileLabel.text('Choose image…');
        $preview.attr('src', '');
        $previewWrap.addClass('d-none');
        $removeImage.prop('checked', false);
        $('input[name="image"]').val('');
    }

    function showPreview(url) {
        if (!url) {
            $previewWrap.addClass('d-none');
            $preview.attr('src', '');
            return;
        }
        $preview.attr('src', url);
        $previewWrap.removeClass('d-none');
    }

    $position.on('change', updateSizeGuide);
    updateSizeGuide();

    $fileInput.on('change', function () {
        const file = this.files && this.files[0];
        $fileLabel.text(file ? file.name : 'Choose image…');
        $removeImage.prop('checked', false);
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                showPreview(e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    $removeImage.on('change', function () {
        if (this.checked) {
            $fileInput.val('');
            $fileLabel.text('Choose image…');
            $previewWrap.addClass('d-none');
        }
    });

    AdminCRUD.initDataTable('#adsTable', {
        ajaxUrl: '{{ route("admin.advertisements.data") }}',
        order: [[0, 'asc']],
        columns: [
            { data: 'name', name: 'name' },
            { data: 'position', name: 'position' },
            { data: 'ad_type', name: 'ad_type' },
            {
                data: 'is_active', name: 'is_active', orderable: false,
                render: (v, t, row) => `<button class="btn btn-sm btn-toggle-active ${v ? 'btn-success' : 'btn-outline-secondary'}" data-id="${row.id}">${v ? 'Active' : 'Inactive'}</button>`,
            },
            { data: 'impressions', name: 'impressions' },
            { data: 'clicks', name: 'clicks' },
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

    $('#btnAddAd').on('click', function (e) {
        e.preventDefault();
        AdminCRUD.openCreate('#adModal', '#adForm', 'Add Advertisement');
        resetImageUi();
        updateSizeGuide();
    });

    AdminCRUD.bindForm({
        formSelector: '#adForm',
        modalSelector: '#adModal',
        buildUrl: (id) => id ? `{{ url('admin/advertisements') }}/${id}` : `{{ route('admin.advertisements.store') }}`,
        buildMethod: (id) => id ? 'PUT' : 'POST',
        onSuccess: function () {
            resetImageUi();
        },
    });

    AdminCRUD.bindEdit('.btn-edit', (id) => `{{ url('admin/advertisements') }}/${id}`, '#adForm', '#adModal', function (data, formSelector) {
        resetImageUi();
        AdminCRUD.autoFill(formSelector, data);
        $('#adModal .modal-title').text('Edit Advertisement');
        updateSizeGuide();

        // External URL only in the text field; uploaded paths stay in preview.
        const isRemote = data.image && /^https?:\/\//i.test(data.image);
        $('input[name="image"]').val(isRemote ? data.image : '');
        showPreview(data.image_url || (isRemote ? data.image : null));
    });

    AdminCRUD.bindDelete('.btn-delete', (id) => `{{ url('admin/advertisements') }}/${id}`);
    AdminCRUD.bindToggle('.btn-toggle-active', (id) => `{{ url('admin/advertisements') }}/${id}/toggle-active`);
});
</script>
@endpush
