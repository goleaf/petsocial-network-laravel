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
        $message = match ($this->type) {
            'reaction' => "{$this->fromUser->name} reacted to your post with {$this->post->reactions->where('user_id', $this->fromUser->id)->first()->type}.",
            'comment' => "{$this->fromUser->name} commented on your post.",
            'mention' => "{$this->fromUser->name} mentioned you in a post.",
            'share' => "{$this->fromUser->name} shared your post.",
            'friend_request' => "{$this->fromUser->name} sent you a friend request.",
            default => '',
        };
        return [
            'type' => $this->type,
            'from_user' => $this->fromUser->name,
            'post_id' => $this->post?->id,
            'message' => $message,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
