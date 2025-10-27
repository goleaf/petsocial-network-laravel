<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\UserDevice;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthController extends Controller
{
    /**
     * Enable 2FA for the authenticated user
     */
    public function enable(Request $request): View
    {
        $user = Auth::user();

        if (! $user->two_factor_secret) {
            // Generate a new secret key and recovery codes for initial setup.
            $google2fa = new Google2FA;
            $secretKey = $google2fa->generateSecretKey();

            $recoveryCodes = [];
            for ($i = 0; $i < 8; $i++) {
                $recoveryCodes[] = Str::random(10);
            }

            $user->update([
                'two_factor_secret' => $secretKey,
                'two_factor_recovery_codes' => $recoveryCodes,
            ]);
        }

        $google2fa = new Google2FA;
        $secretKey = $user->two_factor_secret;

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secretKey
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd
        );

        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrCodeUrl);

        return view('auth.two-factor.enable', [
            'secretKey' => $secretKey,
            'qrCodeSvg' => $qrCodeSvg,
            'recoveryCodes' => $user->two_factor_recovery_codes ?? [],
            'devices' => $user->devices()->latest('last_used_at')->get(),
        ]);
    }

    /**
     * Confirm and activate 2FA
     */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        $google2fa = new Google2FA;

        $valid = $google2fa->verifyKey(
            $user->two_factor_secret,
            $request->code
        );

        if (! $valid) {
            return back()->withErrors([
                'code' => 'The provided code is invalid.',
            ]);
        }

        $user->update([
            'two_factor_enabled' => true,
        ]);

        session(['auth.two_factor.verified' => true]);

        return redirect()->route('settings')->with('status', 'Two-factor authentication has been enabled.');
    }

    /**
     * Disable 2FA for the authenticated user
     */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = Auth::user();
        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
        ]);

        $user->devices()->delete();
        Cookie::queue(Cookie::forget('device_verification'));
        session()->forget('auth.two_factor.verified');

        return redirect()->route('settings')->with('status', 'Two-factor authentication has been disabled.');
    }

    /**
     * Show the 2FA challenge form
     */
    public function challenge(): RedirectResponse|View
    {
        if (session('auth.two_factor.verified')) {
            return redirect()->intended(route('dashboard'));
        }

        return view('auth.two-factor.challenge');
    }

    /**
     * Verify the 2FA code
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string',
            'remember_device' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        $google2fa = new Google2FA;

        // Check if it's a recovery code
        if (strlen($request->code) > 6) {
            $recoveryCodes = $user->two_factor_recovery_codes ?? [];
            $codeIndex = array_search($request->code, $recoveryCodes);

            if ($codeIndex !== false) {
                // Remove the used recovery code
                unset($recoveryCodes[$codeIndex]);
                $user->update([
                    'two_factor_recovery_codes' => array_values($recoveryCodes),
                ]);

                session(['auth.two_factor.verified' => true]);
                $this->rememberDeviceIfRequested($request);

                return redirect()->intended(route('dashboard'));
            }

            return back()->withErrors([
                'code' => 'The provided recovery code is invalid.',
            ]);
        }

        // Verify the code
        $valid = $google2fa->verifyKey(
            $user->two_factor_secret,
            $request->code
        );

        if (! $valid) {
            return back()->withErrors([
                'code' => 'The provided code is invalid.',
            ]);
        }

        session(['auth.two_factor.verified' => true]);
        $this->rememberDeviceIfRequested($request);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Remove a trusted device from the authenticated user.
     */
    public function destroyDevice(UserDevice $device): RedirectResponse
    {
        if ($device->user_id !== Auth::id()) {
            abort(403);
        }

        $device->delete();

        return back()->with('status', 'The device has been removed.');
    }

    /**
     * Persist the trusted device cookie and database record when requested.
     */
    protected function rememberDeviceIfRequested(Request $request): void
    {
        if (! $request->boolean('remember_device')) {
            return;
        }

        $user = Auth::user();

        $plainToken = Str::random(60);
        $hashedToken = hash('sha256', $plainToken);

        $user->devices()->create([
            'name' => Str::limit($request->userAgent() ?: 'Trusted device', 120),
            'token' => $hashedToken,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'last_used_at' => now(),
        ]);

        Cookie::queue(
            Cookie::make(
                'device_verification',
                $plainToken,
                60 * 24 * 30,
                null,
                null,
                config('session.secure', false),
                true,
                false,
                'lax'
            )
        );
    }
}
