<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ActivityNotification extends Notification
{
    public $type;
    public $fromUser;
    public $post;

    public function __construct($type, $fromUser, $post)
    {
        $this->type = $type;
        $this->fromUser = $fromUser;
        $this->post = $post;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => $this->type,
            'from_user' => $this->fromUser->name,
            'post_id' => $this->post->id,
            'message' => $this->type === 'like'
                ? "{$this->fromUser->name} liked your post."
                : "{$this->fromUser->name} commented on your post."
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
