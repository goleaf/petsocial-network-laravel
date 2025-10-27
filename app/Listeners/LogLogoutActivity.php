<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Logout;

/**
 * Track when authenticated users end their session.
 */
class LogLogoutActivity
{
    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        if (! $event->user) {
            return;
        }

        $metadata = [
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'guard' => $event->guard,
        ];

        ActivityLog::record(
            $event->user,
            'logout',
            __('security.logout_description'),
            $metadata
        );
    }
}
