<?php

use Illuminate\Support\Facades\Route;

/**
 * Feature tests exercising the Authenticate middleware through typical web routes.
 */
it('redirects guests to the login route when accessing protected pages', function () {
    // Register a temporary route guarded by the Authenticate middleware to emulate a protected page.
    Route::middleware(['web', 'auth'])->get('/middleware/feature-protected', function () {
        return 'secured';
    });

    // Visit the protected route as a guest and capture the response for verification.
    $response = $this->get('/middleware/feature-protected');

    // Ensure the middleware redirects unauthenticated visitors toward the named login route.
    $response->assertRedirect(route('login'));
});
