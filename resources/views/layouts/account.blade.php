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
