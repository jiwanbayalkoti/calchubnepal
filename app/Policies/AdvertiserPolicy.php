<?php

namespace App\Policies;

use App\Models\Advertiser;
use App\Models\User;

class AdvertiserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('admin');
    }

    public function view(User $user, Advertiser $advertiser): bool
    {
        if ($user->hasRole('super-admin') || $user->hasRole('admin')) {
            return true;
        }

        return $user->isAdvertiser() && (int) $user->advertiser?->id === (int) $advertiser->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super-admin');
    }

    public function update(User $user, Advertiser $advertiser): bool
    {
        // Super admin manages advertisers in admin panel.
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Advertiser may update own profile fields only (portal).
        return $user->isAdvertiser() && (int) $user->advertiser?->id === (int) $advertiser->id;
    }

    public function delete(User $user, Advertiser $advertiser): bool
    {
        return $user->hasRole('super-admin');
    }
}
