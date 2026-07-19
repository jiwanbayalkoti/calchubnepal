<?php

namespace App\Policies;

use App\Models\Calculator;
use App\Models\User;

class CalculatorPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->allowed($user, 'calculators.view');
    }

    public function view(User $user, Calculator $calculator): bool
    {
        return $this->allowed($user, 'calculators.view');
    }

    public function create(User $user): bool
    {
        return $this->allowed($user, 'calculators.create');
    }

    public function update(User $user, Calculator $calculator): bool
    {
        return $this->allowed($user, 'calculators.edit');
    }

    public function delete(User $user, Calculator $calculator): bool
    {
        return $this->allowed($user, 'calculators.delete');
    }

    public function restore(User $user, Calculator $calculator): bool
    {
        return $this->allowed($user, 'calculators.delete');
    }

    public function forceDelete(User $user, Calculator $calculator): bool
    {
        return $this->allowed($user, 'calculators.delete');
    }

    private function allowed(User $user, string $permission): bool
    {
        return $user->hasRole('super-admin')
            || $user->hasRole('admin')
            || $user->hasPermission($permission);
    }
}
