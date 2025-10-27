<?php

use App\Http\Middleware\RedirectIfAuthenticated;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tests\TestCase;

/**
 * Unit tests for the RedirectIfAuthenticated middleware's control flow.
 */
class RedirectIfAuthenticatedTestCase extends TestCase
{
    /**
     * Leverage the standard Laravel application bootstrap for middleware testing.
     */
    // Intentionally left blank; inherits the base behaviour.
}

uses(RedirectIfAuthenticatedTestCase::class);

// Ensure Mockery expectations are cleaned between tests to prevent state leakage.
afterEach(function (): void {
    \Mockery::close();
});

// Validate that authenticated users trigger an immediate redirect to the home route.
it('redirects when the default guard reports an authenticated user', function (): void {
    // Build the middleware instance and an incoming request targeting a guest route.
    $middleware = new RedirectIfAuthenticated();
    $request = Request::create('/login', 'GET');

    // Mock the Auth facade so the default guard reports a signed-in user.
    $guard = \Mockery::mock();
    $guard->shouldReceive('check')->once()->andReturn(true);
    Auth::shouldReceive('guard')->once()->with(null)->andReturn($guard);

    // Invoke the middleware pipeline and capture the resulting response.
    $response = $middleware->handle($request, fn () => new SymfonyResponse('next'));

    // Expect a redirect response targeting the configured home route when authentication is detected.
    expect($response->getStatusCode())->toBe(302)
        ->and($response->isRedirect())->toBeTrue()
        ->and($response->getTargetUrl())->toBe(url(RouteServiceProvider::HOME));
});

// Confirm unauthenticated guards fall through to the next middleware without redirection.
it('passes control to the next middleware when no guard is authenticated', function (): void {
    // Instantiate the middleware and craft a dummy request for a guest-only path.
    $middleware = new RedirectIfAuthenticated();
    $request = Request::create('/register', 'GET');

    // Configure a named guard to simulate a custom authentication check returning false.
    $guard = \Mockery::mock();
    $guard->shouldReceive('check')->once()->andReturn(false);
    Auth::shouldReceive('guard')->once()->with('web')->andReturn($guard);

    // Run the middleware with a closure representing the downstream handler.
    $response = $middleware->handle($request, fn () => new SymfonyResponse('allowed', 200), 'web');

    // Verify the response originates from the next middleware rather than a redirect.
    expect($response->getStatusCode())->toBe(200)
        ->and($response->getContent())->toBe('allowed');
});
