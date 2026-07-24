@extends('layouts.account')

@section('account')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Welcome back, {{ $user->name }}</h1>
            <p class="text-muted-custom mb-0">Track your calculations, favorites and plan in one place.</p>
        </div>
        <a href="{{ route('calculators.index') }}" class="btn btn-brand btn-sm">
            <i class="bi bi-plus-lg"></i> New calculation
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="account-stat card-surface p-3 h-100">
                <div class="stat-label">History</div>
                <div class="stat-value">{{ number_format($historyCount) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="account-stat card-surface p-3 h-100">
                <div class="stat-label">Saved</div>
                <div class="stat-value">
                    {{ number_format($savedCount) }}
                    @if ($savedLimit !== null)
                        <small class="text-muted-custom fs-6">/ {{ $savedLimit }}</small>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="account-stat card-surface p-3 h-100">
                <div class="stat-label">Favorites</div>
                <div class="stat-value">{{ number_format($favoritesCount) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="account-stat card-surface p-3 h-100">
                <div class="stat-label">Plan</div>
                <div class="stat-value fs-5">
                    {{ ($user->isPremiumActive() || $user->isSubscribed()) ? 'Premium' : 'Free' }}
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card-surface p-3 p-md-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 mb-0">Recent calculations</h2>
                    <a href="{{ route('account.history.index') }}" class="small">View all</a>
                </div>

                @forelse ($recentHistory as $item)
                    <div class="account-list-item">
                        <div class="d-flex align-items-center gap-3">
                            <span class="calc-icon" style="width:40px;height:40px;"><i class="bi {{ $item->calculator?->icon ?? 'bi-calculator' }}"></i></span>
                            <div class="min-w-0 flex-grow-1">
                                <a href="{{ $item->calculator ? route('calculators.show', $item->calculator) : '#' }}" class="fw-semibold text-decoration-none d-block text-truncate">
                                    {{ $item->calculator?->title ?? 'Calculator' }}
                                </a>
                                <div class="small text-muted-custom">{{ $item->created_at?->diffForHumans() }}</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted-custom mb-0">No calculations yet. Try a calculator to build your history.</p>
                @endforelse
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card-surface p-3 p-md-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 mb-0">Quick actions</h2>
                </div>
                <div class="d-grid gap-2">
                    <a href="{{ route('account.saved.index') }}" class="btn btn-soft text-start"><i class="bi bi-bookmark-star me-2"></i> Saved results</a>
                    <a href="{{ route('account.qr-codes.index') }}" class="btn btn-soft text-start"><i class="bi bi-qr-code me-2"></i> Dynamic QR codes</a>
                    <a href="{{ route('account.favorites.index') }}" class="btn btn-soft text-start"><i class="bi bi-heart me-2"></i> Favorites</a>
                    <a href="{{ route('account.subscription') }}" class="btn btn-soft text-start"><i class="bi bi-credit-card me-2"></i> Manage plan</a>
                    <a href="{{ route('account.profile.edit') }}" class="btn btn-soft text-start"><i class="bi bi-person-gear me-2"></i> Edit profile</a>
                </div>
            </div>

            <div class="card-surface p-3 p-md-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 mb-0">Favorites</h2>
                    <a href="{{ route('account.favorites.index') }}" class="small">View all</a>
                </div>

                @forelse ($favorites as $favorite)
                    @continue(! $favorite->calculator)
                    <a href="{{ route('calculators.show', $favorite->calculator) }}" class="account-list-item text-decoration-none d-flex align-items-center gap-2">
                        <i class="bi {{ $favorite->calculator->icon ?? 'bi-calculator' }}"></i>
                        <span class="text-truncate">{{ $favorite->calculator->title }}</span>
                    </a>
                @empty
                    <p class="text-muted-custom mb-0 small">Heart a calculator to pin it here.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
