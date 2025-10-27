<?php

use Illuminate\Http\Request;
use Tests\Fixtures\Http\Middleware\TestAuthenticateMiddleware;

/**
 * Unit tests validating the redirect target logic inside the Authenticate middleware.
 */
describe('Authenticate middleware redirect logic', function () {
    it('returns the login route for standard browser requests', function () {
        // Build a typical web request without JSON expectations to evaluate the redirect behaviour.
        $request = Request::create('/middleware/unit-standard', 'GET');

        // Expose the protected redirectTo method so the return value can be asserted directly.
        $middleware = new TestAuthenticateMiddleware(app('auth'));

        // Verify that traditional web traffic is redirected to the canonical login route name.
        expect($middleware->redirectEndpoint($request))->toEqual(route('login'));
    });

    it('returns null for JSON-aware requests so authentication exceptions bubble up', function () {
        // Construct a request advertising JSON expectations to mirror SPA or Livewire calls.
        $request = Request::create('/middleware/unit-json', 'GET', [], [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        // Reuse the exposed middleware helper from the previous scenario.
        $middleware = new TestAuthenticateMiddleware(app('auth'));

        // Confirm the middleware refrains from redirecting so the framework can throw an AuthenticationException.
        expect($middleware->redirectEndpoint($request))->toBeNull();
    });
});
