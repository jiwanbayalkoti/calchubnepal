<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Models\PaymentTransaction;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Manual / invoice-style gateway — activates after admin marks paid,
 * or immediately for free plans. Stripe-ready interface for Phase 4+.
 */
class ManualPaymentGateway implements PaymentGatewayInterface
{
    public function provider(): string
    {
        return 'manual';
    }

    public function checkout(User $user, SubscriptionPlan $plan, array $meta = []): array
    {
        $tx = PaymentTransaction::query()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'provider' => $this->provider(),
            'provider_reference' => 'MAN-'.Str::upper(Str::random(10)),
            'status' => $plan->isFree() ? 'paid' : 'pending',
            'amount' => $plan->price,
            'currency' => $plan->currency ?: 'USD',
            'meta' => $meta,
            'paid_at' => $plan->isFree() ? now() : null,
        ]);

        if ($plan->isFree()) {
            $this->activateSubscription($user, $plan, $tx);
        }

        return [
            'transaction' => $tx,
            'redirect_url' => route('account.subscription'),
            'message' => $plan->isFree()
                ? 'Free plan activated.'
                : 'Payment request created. Complete payment or wait for admin confirmation. Reference: '.$tx->provider_reference,
        ];
    }

    public function complete(PaymentTransaction $transaction, array $payload = []): PaymentTransaction
    {
        if ($transaction->status === 'paid') {
            return $transaction;
        }

        $plan = $transaction->plan;
        if (! $plan) {
            $transaction->update(['status' => 'failed', 'meta' => array_merge($transaction->meta ?? [], ['error' => 'Plan missing'])]);

            return $transaction->refresh();
        }

        $transaction->update([
            'status' => 'paid',
            'paid_at' => now(),
            'meta' => array_merge($transaction->meta ?? [], $payload),
        ]);

        $this->activateSubscription($transaction->user, $plan, $transaction);

        return $transaction->refresh();
    }

    protected function activateSubscription(User $user, SubscriptionPlan $plan, PaymentTransaction $tx): void
    {
        $ends = match ($plan->billing_period) {
            'yearly' => Carbon::now()->addYear(),
            'lifetime' => null,
            default => Carbon::now()->addMonth(),
        };

        $subscription = Subscription::query()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => $ends,
            'payment_reference' => $tx->provider_reference,
            'meta' => ['provider' => $tx->provider],
        ]);

        $tx->update(['subscription_id' => $subscription->id]);

        $user->update([
            'is_premium' => true,
            'premium_expires_at' => $ends,
        ]);
    }
}
