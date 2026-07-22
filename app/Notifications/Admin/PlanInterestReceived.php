<?php

namespace App\Notifications\Admin;

use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PlanInterestReceived extends Notification
{
    use Queueable;

    public function __construct(
        public User $user,
        public SubscriptionPlan $plan,
        public ?string $note = null,
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
        $price = $this->plan->isFree()
            ? 'Free'
            : trim(($this->plan->currency ?? 'USD').' '.number_format((float) $this->plan->price, 2));

        $body = sprintf('%s wants %s (%s)', $this->user->name, $this->plan->name, $price);
        if (filled($this->note)) {
            $body .= ' — '.str($this->note)->limit(60);
        }

        return [
            'category' => 'plan',
            'title' => 'Plan interest',
            'body' => $body,
            'icon' => 'fas fa-tags',
            'url' => route('admin.subscription-plans.index'),
            'user_id' => $this->user->id,
            'user_email' => $this->user->email,
            'plan_id' => $this->plan->id,
            'plan_name' => $this->plan->name,
            'note' => $this->note,
        ];
    }
}
