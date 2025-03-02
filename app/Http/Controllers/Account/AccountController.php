<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AccountController extends Controller
{
    /**
     * Deactivate the user's account
     */
    public function deactivate(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = Auth::user();
        $user->update([
            'deactivated_at' => Carbon::now(),
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'Your account has been deactivated.');
    }

    /**
     * Permanently delete the user's account
     */
    public function delete(Request $request)
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
     * Reactivate a deactivated account
     */
    public function reactivate(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        if (!$user->deactivated_at) {
            return back()->withErrors([
                'email' => 'This account is not deactivated.',
            ]);
        }

        $user->update([
            'deactivated_at' => null,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('status', 'Your account has been reactivated.');
    }

    /**
     * Update the user's password
     */
    public function updatePassword(Request $request)
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
}
