<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use App\Notifications\SecurityEventNotification;
use Illuminate\Auth\Events\PasswordReset;

/**
 * Persist activity details for completed password resets.
 */
class LogPasswordReset
{
    /**
     * Handle the event.
     */
    public function handle(PasswordReset $event): void
    {
        $metadata = [
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ];

        ActivityLog::record(
            $event->user,
            'security_password_reset',
            __('security.password_reset_description'),
            $metadata,
            'critical'
        );

        $event->user->notify(new SecurityEventNotification(
            'password_reset',
            __('security.password_reset_summary'),
            $metadata,
            'critical'
        ));
    }
}
