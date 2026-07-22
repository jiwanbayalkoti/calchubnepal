<?php

namespace App\Notifications\Admin;

use App\Models\Feedback;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FeedbackReceived extends Notification
{
    use Queueable;

    public function __construct(public Feedback $feedback)
    {
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
        $who = $this->feedback->user?->name ?? 'Guest';
        $snippet = str($this->feedback->message)->limit(80)->toString();

        return [
            'category' => 'feedback',
            'title' => 'New feedback ('.$this->feedback->type.')',
            'body' => sprintf('%s: %s', $who, $snippet),
            'icon' => 'fas fa-comment-dots',
            'url' => route('admin.feedback.index'),
            'feedback_id' => $this->feedback->id,
            'rating' => $this->feedback->rating,
        ];
    }
}
