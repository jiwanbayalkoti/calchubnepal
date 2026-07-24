@php
    $user = auth()->user();
@endphp

<div class="account-sidebar card-surface p-3 sticky-lg-top" style="top: 1rem;">
    <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
        <div class="account-avatar">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div class="min-w-0">
            <div class="fw-semibold text-truncate">{{ $user->name }}</div>
            <div class="small text-muted-custom text-truncate">{{ $user->email }}</div>
            @if ($user->isPremiumActive() || $user->isSubscribed())
                <span class="badge bg-warning text-dark mt-1">Premium</span>
            @else
                <span class="badge bg-light text-dark border mt-1">Free</span>
            @endif
        </div>
    </div>

    <nav class="account-nav nav flex-column gap-1">
        <a class="nav-link {{ request()->routeIs('account.dashboard') ? 'active' : '' }}" href="{{ route('account.dashboard') }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a class="nav-link {{ request()->routeIs('account.history.*') ? 'active' : '' }}" href="{{ route('account.history.index') }}">
            <i class="bi bi-clock-history"></i> History
        </a>
        <a class="nav-link {{ request()->routeIs('account.saved.*') ? 'active' : '' }}" href="{{ route('account.saved.index') }}">
            <i class="bi bi-bookmark-star"></i> Saved
        </a>
        <a class="nav-link {{ request()->routeIs('account.favorites.*') ? 'active' : '' }}" href="{{ route('account.favorites.index') }}">
            <i class="bi bi-heart"></i> Favorites
        </a>
        <a class="nav-link {{ request()->routeIs('account.qr-enterprise') ? 'active' : '' }}" href="{{ route('account.qr-enterprise') }}">
            <i class="bi bi-graph-up-arrow"></i> QR Enterprise
        </a>
        <a class="nav-link {{ request()->routeIs('account.qr-codes.*') ? 'active' : '' }}" href="{{ route('account.qr-codes.index') }}">
            <i class="bi bi-qr-code"></i> Dynamic QR
        </a>
        <a class="nav-link {{ request()->routeIs('account.workspaces.*') ? 'active' : '' }}" href="{{ route('account.workspaces.index') }}">
            <i class="bi bi-people"></i> Workspaces
        </a>
        <a class="nav-link {{ request()->routeIs('account.campaigns.*') ? 'active' : '' }}" href="{{ route('account.campaigns.index') }}">
            <i class="bi bi-flag"></i> Campaigns
        </a>
        <a class="nav-link {{ request()->routeIs('account.brand-templates.*') ? 'active' : '' }}" href="{{ route('account.brand-templates.index') }}">
            <i class="bi bi-palette"></i> Brand Templates
        </a>
        <a class="nav-link {{ request()->routeIs('account.bulk-qr.*') ? 'active' : '' }}" href="{{ route('account.bulk-qr.index') }}">
            <i class="bi bi-file-zip"></i> Bulk QR
        </a>
        <a class="nav-link {{ request()->routeIs('account.api-keys.*') ? 'active' : '' }}" href="{{ route('account.api-keys.index') }}">
            <i class="bi bi-key"></i> API Keys
        </a>
        <a class="nav-link {{ request()->routeIs('account.subscription') ? 'active' : '' }}" href="{{ route('account.subscription') }}">
            <i class="bi bi-credit-card"></i> Subscription
        </a>
        <a class="nav-link {{ request()->routeIs('account.profile.*') ? 'active' : '' }}" href="{{ route('account.profile.edit') }}">
            <i class="bi bi-person-gear"></i> Profile
        </a>
        <a class="nav-link" href="{{ route('calculators.index') }}">
            <i class="bi bi-calculator"></i> Browse Calculators
        </a>
        @if ($user->canAccessAdmin())
            <a class="nav-link" href="{{ route('admin.dashboard') }}">
                <i class="bi bi-shield-lock"></i> Admin Panel
            </a>
        @endif
    </nav>

    <form method="POST" action="{{ route('logout') }}" class="mt-3 pt-3 border-top">
        @csrf
        <button type="submit" class="btn btn-outline-brand btn-sm w-100">
            <i class="bi bi-box-arrow-right"></i> Log out
        </button>
    </form>
</div>
