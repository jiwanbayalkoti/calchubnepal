@extends('layouts.admin')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@push('breadcrumbs')
    <li class="breadcrumb-item active">Notifications</li>
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="text-muted">Frontend alerts: contact, feedback, plan interest, new users</span>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="markAllReadBtn">
                <i class="fas fa-check-double mr-1"></i> Mark all read
            </button>
        </div>
        <div class="card-body p-0">
            <ul class="list-group list-group-flush" id="notificationsFullList">
                @forelse ($notifications as $notification)
                    @php($data = (array) $notification->data)
                    <li class="list-group-item {{ $notification->read_at ? '' : 'list-group-item-warning' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="pr-3">
                                <div class="font-weight-bold">
                                    <i class="{{ $data['icon'] ?? 'fas fa-bell' }} mr-1 text-primary"></i>
                                    {{ $data['title'] ?? 'Notification' }}
                                </div>
                                <div class="text-muted">{{ $data['body'] ?? '' }}</div>
                                <small class="text-muted">{{ $notification->created_at?->diffForHumans() }}</small>
                            </div>
                            <div class="text-nowrap">
                                @if (! empty($data['url']))
                                    <a href="{{ $data['url'] }}" class="btn btn-xs btn-primary mark-read-link" data-id="{{ $notification->id }}">Open</a>
                                @endif
                                @if (! $notification->read_at)
                                    <button type="button" class="btn btn-xs btn-outline-secondary mark-read-btn" data-id="{{ $notification->id }}">Mark read</button>
                                @endif
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="list-group-item text-muted">No notifications yet.</li>
                @endforelse
            </ul>
        </div>
        @if ($notifications->hasPages())
            <div class="card-footer">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    function markRead(id, $el) {
        $.post(@json(route('admin.notifications.read', ['id' => '__ID__'])).replace('__ID__', id))
            .done(function () {
                if ($el) {
                    $el.closest('li').removeClass('list-group-item-warning');
                    $el.siblings('.mark-read-btn').remove();
                    $el.filter('.mark-read-btn').remove();
                }
            });
    }

    $(document).on('click', '.mark-read-btn', function () {
        markRead($(this).data('id'), $(this));
    });

    $(document).on('click', '.mark-read-link', function () {
        markRead($(this).data('id'));
    });

    $('#markAllReadBtn').on('click', function () {
        $.post(@json(route('admin.notifications.read-all'))).done(function () {
            toastr.success('All notifications marked as read.');
            location.reload();
        });
    });
});
</script>
@endpush
