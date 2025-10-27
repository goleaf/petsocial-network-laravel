<?php

use Illuminate\Http\Request;
use Livewire\Livewire;
use Tests\Fixtures\Livewire\ProtectedAuthenticateComponent;
use Tests\Fixtures\Http\Middleware\TestAuthenticateMiddleware;

/**
 * Livewire-focused tests confirming the Authenticate middleware suppresses redirects for component requests.
 */
it('treats Livewire requests as JSON so the authenticate middleware avoids redirects', function () {
    // Register the dedicated fixture so Livewire resolves it during the test run.
    Livewire::component('middleware.protected-authenticate', ProtectedAuthenticateComponent::class);

    // Boot the component to obtain Livewire's update endpoint for crafting a follow-up request.
    Livewire::test(ProtectedAuthenticateComponent::class);

    // Mimic a Livewire JSON payload hitting the update endpoint with the identifying headers applied by Livewire.
    $livewireRequest = Request::create(app('livewire')->getUpdateUri(), 'POST', [], [], [], [
        'HTTP_X_LIVEWIRE' => 'true',
        'HTTP_ACCEPT' => 'application/json',
    ]);

    // Run the authenticate middleware against the Livewire request to ensure it yields null instead of a redirect URL.
    $middleware = new TestAuthenticateMiddleware(app('auth'));

    // Livewire requests should skip redirects so framework-level authentication exceptions bubble up cleanly.
    expect($middleware->redirectEndpoint($livewireRequest))->toBeNull();
});

