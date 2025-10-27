<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use App\Notifications\SecurityEventNotification;
use Illuminate\Auth\Events\Failed;

/**
 * Capture metadata for unsuccessful authentication attempts.
 */
class LogFailedLogin
{
    /**
     * Handle the event.
     */
    public function handle(Failed $event): void
    {
        if (! $event->user) {
            return;
        }

        $ipAddress = request()?->ip();
        $userAgent = request()?->userAgent();

        $metadata = [
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'email' => $event->credentials['email'] ?? null,
        ];

        ActivityLog::record(
            $event->user,
            'security_failed_login',
            __('security.failed_login_description'),
            $metadata,
            'warning'
        );

        $event->user->notify(new SecurityEventNotification(
            'failed_login',
            __('security.failed_login_summary'),
            $metadata,
            'warning'
        ));
    }
}
