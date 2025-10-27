<?php

namespace App\Notifications;

use App\Models\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Wraps a stored user notification for email and push delivery.
 */
class SystemNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private UserNotification $notification,
        private string $subject,
        private ?string $actionText,
        private ?string $actionUrl,
        private array $channels
    ) {}

    /**
     * Get the channels the notification should be delivered on.
     */
    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    /**
     * Build the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->subject)
            ->line($this->notification->message);

        if ($this->actionUrl) {
            $mail->action($this->actionText ?? __('notifications.view'), $this->actionUrl);
        }

        return $mail;
    }

    /**
     * Provide data for broadcast / push style delivery.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->notification->id,
            'message' => $this->notification->message,
            'category' => $this->notification->category,
            'priority' => $this->notification->priority,
            'action_url' => $this->actionUrl,
            'action_text' => $this->actionText,
            'created_at' => $this->notification->created_at,
        ]);
    }
}
