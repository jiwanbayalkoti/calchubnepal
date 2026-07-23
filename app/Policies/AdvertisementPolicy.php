<?php

namespace App\Policies;

use App\Models\Advertisement;
use App\Models\User;

class AdvertisementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdvertiser() || $user->canAccessAdmin();
    }

    public function view(User $user, Advertisement $advertisement): bool
    {
        if ($user->canAccessAdmin()) {
            return true;
        }

        return $user->isAdvertiser()
            && $user->advertiser
            && (int) $advertisement->advertiser_id === (int) $user->advertiser->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('admin');
    }

    public function update(User $user, Advertisement $advertisement): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('admin');
    }

    public function delete(User $user, Advertisement $advertisement): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('admin');
    }
}
