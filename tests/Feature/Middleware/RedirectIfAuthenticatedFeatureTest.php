<?php

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;

/**
 * Feature coverage for RedirectIfAuthenticated default guard behaviour.
 */

// Ensure authenticated members are redirected away from the login screen.
it('redirects authenticated users from the login route', function (): void {
    // Create a user to simulate an authenticated session without persisting to the database.
    $member = User::factory()->make(['id' => 1001]);

    // Register a lightweight guest route that mirrors the login middleware behaviour without rendering full views.
    Route::middleware(['web', 'guest'])->get('/guest/login-stub', fn () => 'guest login stub');

    // Authenticate the user before attempting to visit the guest-only route.
    $this->actingAs($member);

    // Visit the guest route which is protected by the RedirectIfAuthenticated middleware.
    $response = $this->get('/guest/login-stub');

    // Confirm the middleware redirects the authenticated user to the dashboard home route.
    $response->assertRedirect(RouteServiceProvider::HOME);
});

// Verify that true guests can continue to access the login screen without interruption.
it('allows guests to access the login route', function (): void {
    // Register the same guest route stub to validate unauthenticated access.
    Route::middleware(['web', 'guest'])->get('/guest/login-stub', fn () => 'guest login stub');

    // Hit the guest route as a visitor without an authenticated session.
    $response = $this->get('/guest/login-stub');

    // Guests should see the simple stub content successfully.
    $response->assertOk();
    $response->assertSee('guest login stub');
});
