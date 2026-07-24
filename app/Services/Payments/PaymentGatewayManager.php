<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Models\SubscriptionPlan;
use App\Models\User;

class PaymentGatewayManager
{
    public function driver(?string $name = null): PaymentGatewayInterface
    {
        $name ??= config('services.payment.default', env('PAYMENT_GATEWAY', 'manual'));

        return match ($name) {
            'stripe' => app(StripePaymentGateway::class),
            default => app(ManualPaymentGateway::class),
        };
    }

    public function checkout(User $user, SubscriptionPlan $plan, array $meta = []): array
    {
        $preferred = filled(env('STRIPE_SECRET')) && ! $plan->isFree() ? 'stripe' : 'manual';

        try {
            return $this->driver($preferred)->checkout($user, $plan, $meta);
        } catch (\Throwable) {
            return $this->driver('manual')->checkout($user, $plan, $meta);
        }
    }
}
