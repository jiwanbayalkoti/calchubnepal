@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'Application Settings')

@push('breadcrumbs')
    <li class="breadcrumb-item active">Settings</li>
@endpush

@section('content')
    <div class="card card-primary card-outline card-outline-tabs">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" role="tablist">
                @foreach ($groups as $group => $settings)
                    <li class="nav-item">
                        <a class="nav-link {{ $loop->first ? 'active' : '' }}" data-toggle="pill" href="#tab-{{ $group }}">{{ ucfirst($group) }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                @foreach ($groups as $group => $settings)
                    <div class="tab-pane {{ $loop->first ? 'show active' : '' }}" id="tab-{{ $group }}">
                        <form class="settings-form" data-group="{{ $group }}">
                            <div class="settings-rows">
                                @forelse ($settings as $setting)
                                    <div class="row align-items-end setting-row mb-2">
                                        <div class="col-md-3">
                                            <label>Key</label>
                                            <input type="text" class="form-control setting-key" value="{{ $setting->key }}" readonly>
                                        </div>
                                        <div class="col-md-5">
                                            <label>Value</label>
                                            <input type="text" class="form-control setting-value" value="{{ $setting->value }}">
                                        </div>
                                        <div class="col-md-2">
                                            <label>Type</label>
                                            <select class="form-control setting-type">
                                                @foreach (['string','integer','float','boolean','array'] as $type)
                                                    <option value="{{ $type }}" {{ $setting->type === $type ? 'selected' : '' }}>{{ $type }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-1 text-center">
                                            <label class="d-block">Public</label>
                                            <input type="checkbox" class="setting-public" {{ $setting->is_public ? 'checked' : '' }}>
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-outline-danger btn-remove-row"><i class="fas fa-times"></i></button>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted">No settings in this group yet. Add one below.</p>
                                @endforelse
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm btn-add-row mt-2">
                                <i class="fas fa-plus"></i> Add Setting
                            </button>
                            <hr>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save {{ ucfirst($group) }} Settings
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    const rowTemplate = `
        <div class="row align-items-end setting-row mb-2">
            <div class="col-md-3"><label>Key</label><input type="text" class="form-control setting-key"></div>
            <div class="col-md-5"><label>Value</label><input type="text" class="form-control setting-value"></div>
            <div class="col-md-2">
                <label>Type</label>
                <select class="form-control setting-type">
                    <option value="string">string</option>
                    <option value="integer">integer</option>
                    <option value="float">float</option>
                    <option value="boolean">boolean</option>
                    <option value="array">array</option>
                </select>
            </div>
            <div class="col-md-1 text-center"><label class="d-block">Public</label><input type="checkbox" class="setting-public"></div>
            <div class="col-md-1"><button type="button" class="btn btn-outline-danger btn-remove-row"><i class="fas fa-times"></i></button></div>
        </div>`;

    $('.btn-add-row').on('click', function () {
        $(this).closest('form').find('.settings-rows').append(rowTemplate);
    });

    $(document).on('click', '.btn-remove-row', function () {
        $(this).closest('.setting-row').remove();
    });

    $('.settings-form').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $form.find('[type=submit]');
        const group = $form.data('group');
        const settings = {};
        const types = {};
        const publicKeys = [];

        $form.find('.setting-row').each(function () {
            const key = $(this).find('.setting-key').val()?.trim();
            if (!key) return;
            settings[key] = $(this).find('.setting-value').val();
            types[key] = $(this).find('.setting-type').val();
            if ($(this).find('.setting-public').is(':checked')) publicKeys.push(key);
        });

        AdminCRUD.toggleLoading($btn, true);

        $.ajax({
            url: '{{ route("admin.settings.save") }}',
            method: 'POST',
            data: { group, settings, types, public: publicKeys },
        })
            .done((res) => toastr.success(res.message || 'Settings saved.'))
            .fail((xhr) => AdminCRUD.notifyError(xhr, 'Unable to save settings.'))
            .always(() => AdminCRUD.toggleLoading($btn, false));
    });
});
</script>
@endpush
