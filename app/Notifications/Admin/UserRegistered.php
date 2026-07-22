<?php

namespace App\Notifications\Admin;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserRegistered extends Notification
{
    use Queueable;

    public function __construct(
        public User $user,
        public string $viaProvider = 'email',
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $provider = $this->viaProvider === 'google' ? 'Google' : 'Email';

        return [
            'category' => 'user',
            'title' => 'New user registered',
            'body' => sprintf('%s (%s) via %s', $this->user->name, $this->user->email, $provider),
            'icon' => 'fas fa-user-plus',
            'url' => route('admin.users.index'),
            'user_id' => $this->user->id,
            'provider' => $this->viaProvider,
        ];
    }
}
