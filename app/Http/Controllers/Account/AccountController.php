<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    /**
     * Deactivate the user's account.
     */
    public function deactivate(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = Auth::user();
        $user->forceFill([
            'deactivated_at' => Carbon::now(),
        ])->save();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'Your account has been deactivated.');
    }

    /**
     * Permanently delete the user's account.
     */
    public function delete(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = Auth::user();

        // Delete related data
        $user->profile()->delete();
        $user->posts()->delete();
        $user->comments()->delete();
        $user->reactions()->delete();
        $user->sentMessages()->delete();
        $user->receivedMessages()->delete();
        $user->shares()->delete();
        $user->sentFriendRequests()->delete();
        $user->receivedFriendRequests()->delete();
        $user->activityLogs()->delete();

        // Delete the user
        $user->delete();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'Your account has been permanently deleted.');
    }

    /**
     * Reactivate a deactivated account with throttling protections.
     */
    public function reactivate(Request $request): RedirectResponse
    {
        $this->ensureIsNotRateLimited($request);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if (! $user->deactivated_at) {
            RateLimiter::clear($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => [__('auth.account_not_deactivated')],
            ]);
        }

        if ($user->isBanned()) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => [__('auth.account_reactivation_banned')],
            ]);
        }

        if ($user->isSuspended()) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => [__('auth.account_reactivation_suspended')],
            ]);
        }

        if (! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'password' => [__('auth.password_mismatch')],
            ]);
        }

        $user->forceFill([
            'deactivated_at' => null,
        ])->save();

        RateLimiter::clear($this->throttleKey($request));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('status', __('auth.account_reactivated'));
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('status', 'Password updated successfully.');
    }

    /**
     * Ensure the reactivation attempts are not rate limited.
     */
    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => [__('auth.account_reactivation_rate_limited', ['seconds' => $seconds])],
        ]);
    }

    /**
     * Build the throttle key for the request.
     */
    protected function throttleKey(Request $request): string
    {
        return Str::lower($request->input('email', '')).'|'.$request->ip();
    }
}
