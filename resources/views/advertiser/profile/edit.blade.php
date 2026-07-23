@extends('layouts.advertiser')

@section('title', 'Profile')
@section('page-title', 'Profile')

@push('breadcrumbs')
    <li class="breadcrumb-item active">Profile</li>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Company Profile</h3></div>
                <form id="profileForm" enctype="multipart/form-data">
                    <div class="card-body">
                        <div class="form-group">
                            <label>Email (read-only)</label>
                            <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                        </div>
                        <div class="form-group">
                            <label>Company Name</label>
                            <input type="text" name="company_name" class="form-control" value="{{ $advertiser->company_name }}" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label>Contact Person</label>
                            <input type="text" name="contact_person" class="form-control" value="{{ $advertiser->contact_person }}" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ $advertiser->phone }}">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label>Company Logo</label>
                            <input type="file" name="logo" class="form-control-file" accept="image/*">
                            <div class="invalid-feedback d-block"></div>
                            @if ($advertiser->logo_url)
                                <img src="{{ $advertiser->logo_url }}" alt="Logo" class="mt-2" style="max-height:64px;">
                            @endif
                        </div>
                        <hr>
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="password" class="form-control" autocomplete="new-password">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$('#profileForm').on('submit', function (e) {
    e.preventDefault();
    const $form = $(this);
    $form.find('.is-invalid').removeClass('is-invalid');
    const fd = new FormData(this);
    $.ajax({
        url: '{{ route('advertiser.profile.update') }}',
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false,
    }).done(function (res) {
        toastr.success(res.message || 'Saved');
    }).fail(function (xhr) {
        if (xhr.status === 422 && xhr.responseJSON?.errors) {
            Object.keys(xhr.responseJSON.errors).forEach(function (key) {
                const $input = $form.find('[name="' + key + '"]');
                $input.addClass('is-invalid');
                $input.siblings('.invalid-feedback').text(xhr.responseJSON.errors[key][0]);
            });
        } else {
            toastr.error('Could not save profile.');
        }
    });
});
</script>
@endpush
