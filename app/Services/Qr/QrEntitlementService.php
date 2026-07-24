<?php

namespace App\Services\Qr;

use App\Models\User;

/**
 * Phase 4 entitlement gates for enterprise QR features.
 */
class QrEntitlementService
{
    public function isEnterprise(User $user): bool
    {
        return $user->canUseQrEnterprise();
    }

    public function maxDynamicQr(User $user): int
    {
        if ($this->isEnterprise($user)) {
            return 10000;
        }

        return 5;
    }

    public function maxApiKeys(User $user): int
    {
        if ($this->isEnterprise($user)) {
            $plan = $user->activeSubscription?->plan;
            if ($plan && (int) $plan->api_rate_limit > 0) {
                return 10;
            }

            return 5;
        }

        return 1;
    }

    public function maxBulkRows(User $user): int
    {
        return $this->isEnterprise($user) ? 500 : 20;
    }

    public function canUseWorkspaces(User $user): bool
    {
        return $this->isEnterprise($user);
    }

    public function canUseWhiteLabel(User $user): bool
    {
        return $this->isEnterprise($user);
    }

    public function canUseBulk(User $user): bool
    {
        return true; // free gets tiny limit; premium full
    }

    public function canUseApiKeys(User $user): bool
    {
        return true;
    }

    public function assertEnterprise(User $user, string $feature = 'this feature'): void
    {
        if (! $this->isEnterprise($user)) {
            throw new \InvalidArgumentException(
                "Upgrade to Premium to use {$feature}."
            );
        }
    }
}
