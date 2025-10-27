<?php

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

// Feature coverage for the login controller to ensure real HTTP flows behave as expected.
describe('Authenticated session feature flows', function (): void {
    it('shows the login view to guests', function (): void {
        // Visiting the login route should render the dedicated authentication view.
        $response = get(route('login'));

        $response->assertOk();
        $response->assertViewIs('auth.login');
    });

    it('authenticates active users and redirects them to the dashboard', function (): void {
        // Create a baseline active account that can successfully authenticate.
        $user = User::factory()->create([
            'email' => 'active@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        // Submit valid credentials and opt-in to the remember me toggle.
        $response = post(route('login'), [
            'email' => 'active@example.com',
            'password' => 'Password123!',
            'remember' => 'on',
        ]);

        $response->assertRedirect(route('dashboard'));
        assertAuthenticatedAs($user->fresh());
    });

    it('prevents deactivated accounts from signing in', function (): void {
        // Seed a user who has previously deactivated their profile.
        $user = User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => Hash::make('Password123!'),
            'deactivated_at' => now(),
        ]);

        // Attempt to log in with valid credentials even though the account is inactive.
        $response = post(route('login'), [
            'email' => 'inactive@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        assertGuest();
    });

    it('blocks suspended accounts and reports the suspension window', function (): void {
        // Build a user who is in the middle of an active suspension.
        $user = User::factory()->create([
            'email' => 'suspended@example.com',
            'password' => Hash::make('Password123!'),
            'suspended_at' => now()->subDay(),
            // Leave the end date null so the controller reports an indefinite suspension without Carbon casting concerns.
            'suspension_ends_at' => null,
        ]);

        // Attempt to authenticate while the suspension is still active.
        $response = post(route('login'), [
            'email' => 'suspended@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        expect(session('errors')->get('email')[0])->toContain('suspended until indefinitely');
        assertGuest();
    });

    it('returns an error for invalid credential attempts', function (): void {
        // Provision a valid user to ensure credential mismatches trigger the fallback branch.
        User::factory()->create([
            'email' => 'valid@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        // Submit an incorrect password so Auth::attempt fails gracefully.
        $response = post(route('login'), [
            'email' => 'valid@example.com',
            'password' => 'WrongPassword!',
        ]);

        $response->assertSessionHasErrors('email');
        expect(session('errors')->get('email')[0])->toBe('The provided credentials do not match our records.');
        assertGuest();
    });
});
