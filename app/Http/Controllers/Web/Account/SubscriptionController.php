<?php

namespace App\Http\Controllers\Web\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\PlanInterestRequest;
use App\Models\PaymentTransaction;
use App\Models\SubscriptionPlan;
use App\Notifications\Admin\PlanInterestReceived;
use App\Services\Admin\AdminNotifier;
use App\Services\Payments\PaymentGatewayManager;
use App\Services\Seo\SeoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SeoService $seo,
        protected AdminNotifier $notifier,
        protected PaymentGatewayManager $payments,
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
            'transactions' => PaymentTransaction::query()->where('user_id', $user->id)->latest()->limit(8)->get(),
            'meta' => $meta,
        ]);
    }

    public function checkout(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subscription_plan_id' => ['required', 'integer', 'exists:subscription_plans,id'],
        ]);

        $plan = SubscriptionPlan::query()->where('is_active', true)->findOrFail($data['subscription_plan_id']);

        try {
            $result = $this->payments->checkout($request->user(), $plan);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        if (! empty($result['redirect_url']) && ($result['transaction']->provider ?? '') === 'stripe') {
            return redirect()->away($result['redirect_url']);
        }

        return redirect()
            ->route('account.subscription')
            ->with('status', $result['message'] ?? 'Checkout started.');
    }

    public function billingSuccess(Request $request, PaymentTransaction $transaction): RedirectResponse
    {
        abort_unless((int) $transaction->user_id === (int) $request->user()->id, 403);

        if ($transaction->status !== 'paid') {
            $this->payments->driver($transaction->provider)->complete($transaction, [
                'session_id' => $request->query('session_id'),
            ]);
        }

        return redirect()
            ->route('account.subscription')
            ->with('status', 'Payment confirmed. Premium features unlocked.');
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
