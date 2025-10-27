<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use App\Notifications\SecurityEventNotification;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Cookie;

/**
 * Record audit details for successful authentication events.
 */
class LogSuccessfulLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $ipAddress = request()?->ip();
        $userAgent = request()?->userAgent();

        $this->satisfyDeviceChallenge($event, $ipAddress, $userAgent);

        $metadata = [
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'guard' => $event->guard,
            'remember' => $event->remember,
        ];

        $logEntry = ActivityLog::record(
            $event->user,
            'login',
            __('security.login_success_description'),
            $metadata
        );

        $hasSeenDevice = ActivityLog::query()
            ->where('user_id', $event->user->id)
            ->where('action', 'login')
            ->where('id', '!=', $logEntry->id)
            ->when($ipAddress, fn ($query) => $query->where('ip_address', $ipAddress))
            ->when($userAgent, fn ($query) => $query->where('user_agent', $userAgent))
            ->exists();

        if (! $hasSeenDevice && ($ipAddress || $userAgent)) {
            $event->user->notify(new SecurityEventNotification(
                'new_device_login',
                __('security.new_device_summary'),
                $metadata,
                'warning'
            ));
        }
    }

    /**
     * Attempt to satisfy the MFA challenge automatically using a trusted device token.
     */
    protected function satisfyDeviceChallenge(Login $event, ?string $ipAddress, ?string $userAgent): void
    {
        $request = request();

        if (! $event->user || ! $event->user->two_factor_enabled || ! $request) {
            return;
        }

        $deviceToken = $request->cookie('device_verification');

        if (! $deviceToken) {
            return;
        }

        $device = $event->user->devices()->where('token', hash('sha256', $deviceToken))->first();

        if ($device === null) {
            Cookie::queue(Cookie::forget('device_verification'));

            return;
        }

        session(['auth.two_factor.verified' => true]);

        $device->forceFill([
            'last_used_at' => now(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ])->save();
    }
}
