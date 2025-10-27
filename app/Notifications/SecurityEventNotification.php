<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification broadcast for security-sensitive account events.
 */
class SecurityEventNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $eventType,
        public string $summary,
        public array $context = [],
        public string $severity = 'info'
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Build the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->subject(__('security.notification_subject', ['app' => config('app.name')]))
            ->greeting(__('security.notification_greeting', ['name' => $notifiable->name]))
            ->line($this->summary)
            ->line(__('security.notification_event_type', ['type' => $this->eventType]))
            ->line(__('security.notification_severity', ['level' => ucfirst($this->severity)]))
            ->line(__('security.notification_timestamp', ['time' => now()->toDayDateTimeString()]));

        if (isset($this->context['ip_address'])) {
            $mailMessage->line(__('security.notification_ip', ['ip' => $this->context['ip_address']]));
        }

        if (isset($this->context['user_agent'])) {
            $mailMessage->line(__('security.notification_user_agent', ['agent' => $this->context['user_agent']]));
        }

        return $mailMessage->line(__('security.notification_footer'));
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event_type' => $this->eventType,
            'summary' => $this->summary,
            'context' => $this->context,
            'severity' => $this->severity,
        ];
    }
}
