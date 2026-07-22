<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class AdminNotifier
{
    /**
     * Send a database notification to every active admin user.
     */
    public function notify(Notification $notification): void
    {
        $this->recipients()->each(function (User $admin) use ($notification): void {
            $admin->notify($notification);
        });
    }

    /**
     * @return Collection<int, User>
     */
    protected function recipients(): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->where(function ($query): void {
                $query->whereHas('primaryRole', fn ($role) => $role->whereIn('slug', ['super-admin', 'admin']))
                    ->orWhereHas('roles', fn ($role) => $role->whereIn('slug', ['super-admin', 'admin']));
            })
            ->get();
    }
}
