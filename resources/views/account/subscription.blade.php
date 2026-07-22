@extends('layouts.account')

@section('account')
    <h1 class="h3 mb-1">Subscription</h1>
    <p class="text-muted-custom mb-4">Your current plan and available upgrades.</p>

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
                        {{ $isPremium ? 'Premium access is active.' : 'Upgrade anytime to unlock more saved results and premium tools.' }}
                    </div>
                @endif
            </div>
            @unless ($isPremium)
                <a href="{{ route('pricing') }}" class="btn btn-brand">View pricing</a>
            @endunless
        </div>
    </div>

    <h2 class="h5 mb-3">Available plans</h2>
    <div class="row g-3">
        @forelse ($plans as $plan)
            <div class="col-md-6 col-xl-4">
                <div class="card-surface p-3 h-100 d-flex flex-column">
                    <div class="fw-semibold">{{ $plan->name }}</div>
                    <div class="display-6 my-2" style="font-size:1.75rem;">
                        {{ $plan->currency ?? 'USD' }} {{ number_format((float) $plan->price, 2) }}
                        <small class="fs-6 text-muted-custom">/ {{ $plan->billing_period ?? 'month' }}</small>
                    </div>
                    <p class="small text-muted-custom flex-grow-1">{{ $plan->description }}</p>
                    @if (is_array($plan->features) && count($plan->features))
                        <ul class="small ps-3 mb-3">
                            @foreach ($plan->features as $feature)
                                <li>{{ is_string($feature) ? $feature : json_encode($feature) }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if ($plan->isFree())
                        <span class="btn btn-sm btn-soft disabled">Current free tier</span>
                    @else
                        <form action="{{ route('account.subscription.interest') }}" method="POST" class="js-plan-interest-form mt-auto">
                            @csrf
                            <input type="hidden" name="subscription_plan_id" value="{{ $plan->id }}">
                            <button type="submit" class="btn btn-sm btn-outline-brand w-100">Request this plan</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card-surface p-4 text-muted-custom">
                    Plans will appear here once published. See the <a href="{{ route('pricing') }}">pricing page</a> for details.
                </div>
            </div>
        @endforelse
    </div>
@endsection
