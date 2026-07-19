<?php

namespace App\Http\Controllers\Web\Account;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Services\Seo\SeoService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SeoService $seo,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $subscription = $user->activeSubscription()->with('plan')->first();
        $plans = SubscriptionPlan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $meta = $this->seo->buildMeta(null, [
            'title' => 'Subscription — AI Calculator Hub',
            'description' => 'View your plan and available upgrades.',
            'canonical' => route('account.subscription'),
            'robots' => 'noindex,nofollow',
        ]);

        return view('account.subscription', [
            'user' => $user,
            'subscription' => $subscription,
            'plans' => $plans,
            'isPremium' => $user->isPremiumActive() || $user->isSubscribed(),
            'meta' => $meta,
        ]);
    }
}
