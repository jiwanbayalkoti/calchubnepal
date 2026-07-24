<?php

namespace App\Contracts\Payments;

use App\Models\PaymentTransaction;
use App\Models\SubscriptionPlan;
use App\Models\User;

interface PaymentGatewayInterface
{
    public function provider(): string;

    /**
     * Start checkout for a plan. Returns redirect URL or instructions.
     *
     * @return array{transaction: PaymentTransaction, redirect_url: ?string, message: string}
     */
    public function checkout(User $user, SubscriptionPlan $plan, array $meta = []): array;

    /**
     * Confirm / finalize a pending transaction (webhook or manual).
     */
    public function complete(PaymentTransaction $transaction, array $payload = []): PaymentTransaction;
}
