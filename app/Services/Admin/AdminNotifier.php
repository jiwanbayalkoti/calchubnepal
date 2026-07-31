<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class AdminNotifier
{
    /**
     * Send a database notification to every active admin user.
     * Recipients match EnsureUserIsAdmin: super-admin, admin, or admin.dashboard.view.
     */
    public function notify(Notification $notification): void
    {
        $recipients = $this->recipients();

        if ($recipients->isEmpty()) {
            Log::warning('AdminNotifier: no admin recipients for notification.', [
                'notification' => $notification::class,
            ]);

            return;
        }

        // Fresh instance per recipient avoids shared notification id collisions.
        NotificationFacade::send($recipients, $notification);
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
                    ->orWhereHas('roles', fn ($role) => $role->whereIn('slug', ['super-admin', 'admin']))
                    ->orWhereHas('primaryRole.permissions', fn ($p) => $p->where('slug', 'admin.dashboard.view'))
                    ->orWhereHas('roles.permissions', fn ($p) => $p->where('slug', 'admin.dashboard.view'));
            })
            ->get()
            ->unique('id')
            ->values();
    }
}
