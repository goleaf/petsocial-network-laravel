<?php

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * HTTP tests validating RedirectIfAuthenticated across custom guards.
 */

// Confirm the middleware honours guard parameters when protecting alternate login routes.
it('redirects authenticated admin guard users from a custom login endpoint', function (): void {
    // Register a temporary route that mimics an admin login form protected by the guest middleware for the admin guard.
    Route::middleware(['web', 'guest:admin'])->get('/admin/login-stub', function () {
        // Render a minimal response for unauthenticated visitors.
        return 'admin login';
    });

    // Ensure the admin guard configuration is available for the simulated route.
    config()->set('auth.guards.admin', [
        'driver' => 'session',
        'provider' => 'users',
    ]);

    // Create an administrator user record for authentication.
    $admin = User::factory()->make(['id' => 2001]);

    // Authenticate the user using the admin guard prior to hitting the guest-only route.
    $this->actingAs($admin, 'admin');

    // Perform an HTTP GET request against the admin login route while authenticated.
    $response = $this->get('/admin/login-stub');

    // The middleware should redirect the authenticated guard-specific user to the shared home route.
    $response->assertRedirect(RouteServiceProvider::HOME);
});

// Validate that guests targeting the custom guard route receive the expected content instead of a redirect.
it('allows guests to access the custom admin login endpoint', function (): void {
    // Register the same temporary route for guest access.
    Route::middleware(['web', 'guest:admin'])->get('/admin/login-stub', function () {
        return 'admin login';
    });

    // Ensure the guest guard definition exists for the custom admin route while testing unauthenticated access.
    config()->set('auth.guards.admin', [
        'driver' => 'session',
        'provider' => 'users',
    ]);

    // Hit the custom login endpoint as a guest user with HTTP headers typical for a browser.
    $response = $this->get('/admin/login-stub', ['Accept' => 'text/html']);

    // Confirm the guest user can view the placeholder content successfully.
    $response->assertOk();
    $response->assertSee('admin login');
});
