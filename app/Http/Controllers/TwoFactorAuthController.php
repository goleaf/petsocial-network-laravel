<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Str;

class TwoFactorAuthController extends Controller
{
    /**
     * Enable 2FA for the authenticated user
     */
    public function enable(Request $request)
    {
        $user = Auth::user();
        
        // Generate a new secret key
        $google2fa = new Google2FA();
        $secretKey = $google2fa->generateSecretKey();
        
        // Generate recovery codes
        $recoveryCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = Str::random(10);
        }
        
        // Store the secret key and recovery codes
        $user->update([
            'two_factor_secret' => $secretKey,
            'two_factor_recovery_codes' => $recoveryCodes,
        ]);
        
        // Generate QR code
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secretKey
        );
        
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrCodeUrl);
        
        return view('auth.two-factor.enable', [
            'secretKey' => $secretKey,
            'qrCodeSvg' => $qrCodeSvg,
            'recoveryCodes' => $recoveryCodes,
        ]);
    }
    
    /**
     * Confirm and activate 2FA
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);
        
        $user = Auth::user();
        $google2fa = new Google2FA();
        
        $valid = $google2fa->verifyKey(
            $user->two_factor_secret,
            $request->code
        );
        
        if (!$valid) {
            return back()->withErrors([
                'code' => 'The provided code is invalid.',
            ]);
        }
        
        $user->update([
            'two_factor_enabled' => true,
        ]);
        
        return redirect()->route('settings')->with('status', 'Two-factor authentication has been enabled.');
    }
    
    /**
     * Disable 2FA for the authenticated user
     */
    public function disable(Request $request)
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
        
        return redirect()->route('settings')->with('status', 'Two-factor authentication has been disabled.');
    }
    
    /**
     * Show the 2FA challenge form
     */
    public function challenge()
    {
        return view('auth.two-factor.challenge');
    }
    
    /**
     * Verify the 2FA code
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);
        
        $user = Auth::user();
        $google2fa = new Google2FA();
        
        // Check if it's a recovery code
        if (strlen($request->code) > 6) {
            $recoveryCodes = $user->two_factor_recovery_codes;
            $codeIndex = array_search($request->code, $recoveryCodes);
            
            if ($codeIndex !== false) {
                // Remove the used recovery code
                unset($recoveryCodes[$codeIndex]);
                $user->update([
                    'two_factor_recovery_codes' => array_values($recoveryCodes),
                ]);
                
                session(['auth.two_factor.verified' => true]);
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
        
        if (!$valid) {
            return back()->withErrors([
                'code' => 'The provided code is invalid.',
            ]);
        }
        
        session(['auth.two_factor.verified' => true]);
        return redirect()->intended(route('dashboard'));
    }
}
