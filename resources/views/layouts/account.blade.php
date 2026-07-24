@extends('layouts.public')

@section('content')
    <section class="section atmosphere pt-4 pb-5">
        <div class="container">
            <div class="row g-4">
                <aside class="col-lg-3">
                    @include('account.partials.sidebar')
                </aside>
                <div class="col-lg-9">
                    @if (session('status'))
                        <div class="alert alert-success account-alert mb-3" role="status">
                            @switch(session('status'))
                                @case('profile-updated') Profile updated successfully. @break
                                @case('password-updated') Password updated successfully. @break
                                @case('history-deleted') History entry removed. @break
                                @case('history-cleared') Calculation history cleared. @break
                                @case('favorite-added') Added to favorites. @break
                                @case('favorite-removed') Removed from favorites. @break
                                @case('calculation-saved') Calculation saved. @break
                                @case('saved-deleted') Saved calculation deleted. @break
                                @case('qr-updated') Dynamic QR updated. @break
                                @case('qr-deleted') Dynamic QR deleted. @break
                                @case('qr-paused') Dynamic QR paused. @break
                                @case('qr-resumed') Dynamic QR resumed. @break
                                @case('workspace-created') Workspace created. @break
                                @case('workspace-updated') Workspace updated. @break
                                @case('member-invited') Team member invited. @break
                                @case('member-updated') Member role updated. @break
                                @case('member-removed') Member removed. @break
                                @case('template-created') Brand template saved. @break
                                @case('template-deleted') Brand template deleted. @break
                                @case('campaign-created') Campaign created. @break
                                @case('campaign-deleted') Campaign deleted. @break
                                @case('bulk-completed') Bulk QR job completed. Download your ZIP below. @break
                                @case('api-key-created') API key created — copy it now; it won’t be shown again. @break
                                @case('api-key-toggled') API key status updated. @break
                                @case('api-key-updated') API key status updated. @break
                                @case('api-key-deleted') API key deleted. @break
                                @case('api-key-revoked') API key revoked. @break
                                @default {{ session('status') }}
                            @endswitch
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger account-alert mb-3" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    @yield('account')
                </div>
            </div>
        </div>
    </section>
@endsection
