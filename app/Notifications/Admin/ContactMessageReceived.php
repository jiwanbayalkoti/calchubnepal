<?php

namespace App\Notifications\Admin;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContactMessageReceived extends Notification
{
    use Queueable;

    public function __construct(public ContactMessage $message)
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
        return [
            'category' => 'contact',
            'title' => 'New contact message',
            'body' => sprintf('%s — %s', $this->message->name, $this->message->subject),
            'icon' => 'fas fa-envelope',
            'url' => route('admin.contact-messages.index'),
            'contact_message_id' => $this->message->id,
            'email' => $this->message->email,
        ];
    }
}
