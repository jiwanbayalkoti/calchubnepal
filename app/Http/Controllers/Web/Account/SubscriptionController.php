<?php

namespace App\Http\Controllers\Web\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\PlanInterestRequest;
use App\Models\SubscriptionPlan;
use App\Notifications\Admin\PlanInterestReceived;
use App\Services\Admin\AdminNotifier;
use App\Services\Seo\SeoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SeoService $seo,
        protected AdminNotifier $notifier,
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

    public function requestPlan(PlanInterestRequest $request): RedirectResponse|JsonResponse
    {
        $plan = SubscriptionPlan::query()
            ->where('is_active', true)
            ->findOrFail($request->validated('subscription_plan_id'));

        $this->notifier->notify(new PlanInterestReceived(
            $request->user(),
            $plan,
            $request->validated('note'),
        ));

        $message = 'Thanks! We received your interest in '.$plan->name.' and will follow up soon.';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('status', $message);
    }
}
