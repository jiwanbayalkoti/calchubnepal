@extends('layouts.admin')

@section('title', 'SEO Redirects')
@section('page-title', 'SEO Redirects')

@push('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.seo.audit') }}">SEO</a></li>
    <li class="breadcrumb-item active">Redirects</li>
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">301 / 302 Redirect Manager</h3>
            <button type="button" class="btn btn-sm btn-primary" id="btnAddRedirect"><i class="fas fa-plus"></i> Add Redirect</button>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="redirectsTable" style="width:100%">
                <thead>
                    <tr>
                        <th>From Path</th>
                        <th>To URL</th>
                        <th>Code</th>
                        <th>Active</th>
                        <th>Hits</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="redirectModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="redirectForm" class="modal-content">
                @csrf
                <input type="hidden" name="id" id="redirectId">
                <div class="modal-header">
                    <h5 class="modal-title">Redirect</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>From path</label>
                        <input type="text" name="from_path" id="from_path" class="form-control" placeholder="/old-page" required>
                    </div>
                    <div class="form-group">
                        <label>To URL</label>
                        <input type="text" name="to_url" id="to_url" class="form-control" placeholder="/new-page or https://..." required>
                    </div>
                    <div class="form-group">
                        <label>Status code</label>
                        <select name="status_code" id="status_code" class="form-control">
                            <option value="301">301 Permanent</option>
                            <option value="302">302 Temporary</option>
                            <option value="307">307</option>
                            <option value="308">308</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Note</label>
                        <input type="text" name="note" id="note" class="form-control">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="is_active" id="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    const table = $('#redirectsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('admin.seo.redirects.data') }}',
        columns: [
            { data: 'from_path' },
            { data: 'to_url' },
            { data: 'status_code' },
            { data: 'is_active', render: v => v ? 'Yes' : 'No' },
            { data: 'hit_count' },
            { data: null, orderable: false, render: (r) => `
                <button class="btn btn-xs btn-info js-edit" data-id="${r.id}" data-from="${r.from_path}" data-to="${r.to_url}" data-code="${r.status_code}" data-active="${r.is_active ? 1 : 0}" data-note="${r.note || ''}">Edit</button>
                <button class="btn btn-xs btn-danger js-del" data-id="${r.id}">Delete</button>` }
        ]
    });

    $('#btnAddRedirect').on('click', function () {
        $('#redirectForm')[0].reset();
        $('#redirectId').val('');
        $('#is_active').prop('checked', true);
        $('#redirectModal').modal('show');
    });

    $('#redirectsTable').on('click', '.js-edit', function () {
        const b = $(this);
        $('#redirectId').val(b.data('id'));
        $('#from_path').val(b.data('from'));
        $('#to_url').val(b.data('to'));
        $('#status_code').val(b.data('code'));
        $('#note').val(b.data('note'));
        $('#is_active').prop('checked', String(b.data('active')) === '1');
        $('#redirectModal').modal('show');
    });

    $('#redirectsTable').on('click', '.js-del', function () {
        const id = $(this).data('id');
        Swal.fire({ title: 'Delete redirect?', icon: 'warning', showCancelButton: true }).then(res => {
            if (!res.isConfirmed) return;
            $.ajax({
                url: '{{ url('admin/seo/redirects') }}/' + id,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: () => { toastr.success('Deleted'); table.ajax.reload(); }
            });
        });
    });

    $('#redirectForm').on('submit', function (e) {
        e.preventDefault();
        const id = $('#redirectId').val();
        const payload = {
            _token: '{{ csrf_token() }}',
            from_path: $('#from_path').val(),
            to_url: $('#to_url').val(),
            status_code: $('#status_code').val(),
            note: $('#note').val(),
            is_active: $('#is_active').is(':checked') ? 1 : 0,
        };
        $.ajax({
            url: id ? '{{ url('admin/seo/redirects') }}/' + id : '{{ route('admin.seo.redirects.store') }}',
            method: id ? 'PUT' : 'POST',
            data: payload,
            success: () => {
                toastr.success('Saved');
                $('#redirectModal').modal('hide');
                table.ajax.reload();
            },
            error: xhr => toastr.error(xhr.responseJSON?.message || 'Save failed')
        });
    });
});
</script>
@endpush
