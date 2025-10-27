<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\post;

// RefreshDatabase is layered on top of the shared TestCase registration from Pest.php.
uses(RefreshDatabase::class);

// Test deactivated accounts cannot authenticate without reactivation.
it('blocks login attempts for deactivated accounts', function (): void {
    $user = User::factory()->create([
        'email' => 'deactivated@example.com',
        'password' => Hash::make('Password123!'),
        'deactivated_at' => now(),
    ]);

    $response = post(route('login'), [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors('email');
    expect(auth()->check())->toBeFalse();
});

// Test valid reactivation restores the account and authenticates the user.
it('reactivates a deactivated account with correct credentials', function (): void {
    $user = User::factory()->create([
        'email' => 'reactivate@example.com',
        'password' => Hash::make('SecurePass!1'),
        'deactivated_at' => now(),
    ]);

    $response = post(route('account.reactivate.post'), [
        'email' => $user->email,
        'password' => 'SecurePass!1',
    ]);

    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('status', __('auth.account_reactivated'));
    assertAuthenticatedAs($user->fresh());
    expect($user->fresh()->deactivated_at)->toBeNull();
});

// Test rate limiting prevents brute-force reactivation attempts.
it('rate limits repeated failed reactivation attempts', function (): void {
    $user = User::factory()->create([
        'email' => 'limit@example.com',
        'password' => Hash::make('CorrectHorseBatteryStaple1!'),
        'deactivated_at' => now(),
    ]);

    RateLimiter::clear(strtolower($user->email).'|'.'127.0.0.1');

    foreach (range(1, 5) as $attempt) {
        $response = post(route('account.reactivate.post'), [
            'email' => $user->email,
            'password' => 'WrongPassword!',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    $response = post(route('account.reactivate.post'), [
        'email' => $user->email,
        'password' => 'WrongPassword!',
    ]);

    $response->assertSessionHasErrors(['email']);
    expect(session('errors')->get('email')[0])->toContain('Too many reactivation attempts.');
});
