@extends('layouts.account')

@section('account')
    <h1 class="h3 mb-1">Subscription</h1>
    <p class="text-muted-custom mb-4">Your current plan and available upgrades. Premium unlocks workspaces, white label, higher bulk/API limits.</p>

    <div class="card-surface p-3 p-md-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <div class="small text-muted-custom text-uppercase fw-bold">Current plan</div>
                <div class="h4 mb-1">
                    @if ($subscription?->plan)
                        {{ $subscription->plan->name }}
                    @elseif ($isPremium)
                        Premium
                    @else
                        Free
                    @endif
                </div>
                @if ($subscription?->ends_at)
                    <div class="small text-muted-custom">Renews / ends {{ $subscription->ends_at->format('M j, Y') }}</div>
                @elseif ($user->premium_expires_at)
                    <div class="small text-muted-custom">Premium until {{ $user->premium_expires_at->format('M j, Y') }}</div>
                @else
                    <div class="small text-muted-custom">
                        {{ $isPremium ? 'Premium access is active.' : 'Upgrade anytime to unlock enterprise QR tools.' }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <h2 class="h5 mb-3">Available plans</h2>
    <div class="row g-3 mb-4">
        @forelse ($plans as $plan)
            <div class="col-md-6 col-xl-4">
                <div class="card-surface p-3 h-100 d-flex flex-column">
                    <h3 class="h5">{{ $plan->name }}</h3>
                    <div class="mb-2">
                        <span class="h4 mb-0">{{ $plan->currency }} {{ number_format((float) $plan->price, 2) }}</span>
                        <span class="small text-muted-custom">/ {{ $plan->billing_period }}</span>
                    </div>
                    <p class="small text-muted-custom flex-grow-1">{{ $plan->description }}</p>
                    <ul class="small mb-3">
                        @foreach(($plan->features ?? []) as $feature)
                            <li>{{ $feature }}</li>
                        @endforeach
                    </ul>
                    @if(!$plan->isFree())
                        <form method="POST" action="{{ route('account.subscription.checkout') }}" class="mb-2">
                            @csrf
                            <input type="hidden" name="subscription_plan_id" value="{{ $plan->id }}">
                            <button class="btn btn-brand btn-sm w-100">Checkout</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('account.subscription.interest') }}">
                        @csrf
                        <input type="hidden" name="subscription_plan_id" value="{{ $plan->id }}">
                        <button class="btn btn-soft btn-sm w-100">Request / ask sales</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-muted-custom">No plans configured.</p>
        @endforelse
    </div>

    @if(($transactions ?? collect())->isNotEmpty())
        <h2 class="h5 mb-3">Recent payments</h2>
        <div class="card-surface p-3">
            @foreach($transactions as $tx)
                <div class="d-flex justify-content-between small py-2 border-bottom">
                    <span>{{ $tx->provider }} · {{ $tx->provider_reference }}</span>
                    <span>{{ $tx->status }} · {{ $tx->currency }} {{ $tx->amount }}</span>
                </div>
            @endforeach
        </div>
    @endif
@endsection
