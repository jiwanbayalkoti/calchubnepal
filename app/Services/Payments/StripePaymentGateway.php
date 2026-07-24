<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Models\PaymentTransaction;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Optional Stripe Checkout Session gateway (no Cashier package).
 * Enabled when STRIPE_SECRET is set; otherwise ManualPaymentGateway is used.
 */
class StripePaymentGateway implements PaymentGatewayInterface
{
    public function provider(): string
    {
        return 'stripe';
    }

    public function checkout(User $user, SubscriptionPlan $plan, array $meta = []): array
    {
        $secret = (string) config('services.stripe.secret', env('STRIPE_SECRET'));
        if ($secret === '') {
            throw new InvalidArgumentException('Stripe is not configured.');
        }

        $tx = PaymentTransaction::query()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'provider' => $this->provider(),
            'provider_reference' => 'STR-'.Str::upper(Str::random(10)),
            'status' => 'pending',
            'amount' => $plan->price,
            'currency' => strtoupper($plan->currency ?: 'USD'),
            'meta' => $meta,
        ]);

        $amountCents = (int) round(((float) $plan->price) * 100);
        $response = Http::withToken($secret)
            ->asForm()
            ->post('https://api.stripe.com/v1/checkout/sessions', [
                'mode' => 'payment',
                'success_url' => route('account.billing.success', ['transaction' => $tx->uuid]).'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('account.subscription'),
                'client_reference_id' => $tx->uuid,
                'customer_email' => $user->email,
                'line_items[0][price_data][currency]' => strtolower($plan->currency ?: 'usd'),
                'line_items[0][price_data][product_data][name]' => $plan->name,
                'line_items[0][price_data][unit_amount]' => $amountCents,
                'line_items[0][quantity]' => 1,
            ]);

        if (! $response->successful()) {
            $tx->update(['status' => 'failed', 'meta' => ['error' => $response->json()]]);
            throw new InvalidArgumentException('Unable to start Stripe checkout.');
        }

        $session = $response->json();
        $tx->update([
            'meta' => array_merge($tx->meta ?? [], ['stripe_session_id' => $session['id'] ?? null]),
            'provider_reference' => $session['id'] ?? $tx->provider_reference,
        ]);

        return [
            'transaction' => $tx->refresh(),
            'redirect_url' => $session['url'] ?? null,
            'message' => 'Redirecting to Stripe Checkout…',
        ];
    }

    public function complete(PaymentTransaction $transaction, array $payload = []): PaymentTransaction
    {
        return app(ManualPaymentGateway::class)->complete($transaction, $payload);
    }
}
