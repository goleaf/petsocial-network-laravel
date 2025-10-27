<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorIsVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user !== null && $user->two_factor_enabled) {
            if (! $request->session()->get('auth.two_factor.verified', false)) {
                if ($this->verifyTrustedDevice($request)) {
                    return $next($request);
                }

                if ($request->routeIs('two-factor.challenge', 'two-factor.verify', 'logout')) {
                    return $next($request);
                }

                return redirect()->route('two-factor.challenge');
            }
        }

        return $next($request);
    }

    /**
     * Validate the trusted device cookie and mark the session as verified when appropriate.
     */
    protected function verifyTrustedDevice(Request $request): bool
    {
        $token = $request->cookie('device_verification');

        if (! $token) {
            return false;
        }

        $user = $request->user();

        if ($user === null) {
            return false;
        }

        $hashedToken = hash('sha256', $token);

        $device = $user->devices()->where('token', $hashedToken)->first();

        if ($device === null) {
            Cookie::queue(Cookie::forget('device_verification'));

            return false;
        }

        $device->forceFill([
            'last_used_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ])->save();

        $request->session()->put('auth.two_factor.verified', true);

        return true;
    }
}
