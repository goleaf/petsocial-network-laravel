<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
                if ($request->routeIs('two-factor.challenge', 'two-factor.verify', 'logout')) {
                    return $next($request);
                }

                return redirect()->route('two-factor.challenge');
            }
        }

        return $next($request);
    }
}
