<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\withoutVite;

/**
 * HTTP level verification for the pet management entry point.
 */
it('redirects guests to the login screen when visiting the pet management route', function (): void {
    // Guests should be bounced to the login form because the route is protected by auth middleware.
    get('/pets')->assertRedirect(route('login'));
});

it('returns a successful response for authenticated visitors', function (): void {
    // Sign in a user so the request passes the middleware guard.
    $user = User::factory()->create();
    actingAs($user);

    // Confirm authenticated requests receive a 200-level response from the Livewire endpoint.
    withoutVite();
    get('/pets')->assertOk();
});
