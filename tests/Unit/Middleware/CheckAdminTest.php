<?php

use App\Http\Middleware\CheckAdmin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\actingAs;

/**
 * Unit level coverage for the CheckAdmin middleware guard clauses.
 */
it('redirects guests to the home page when no user is authenticated', function (): void {
    // Instantiate the middleware so the handle method can be exercised directly.
    $middleware = new CheckAdmin();

    // Create a simulated request targeting an admin-only endpoint.
    $request = Request::create('/admin/dashboard', 'GET');

    // Execute the middleware pipeline and capture the response for assertions.
    $response = $middleware->handle($request, function () use ($request): Response {
        return response('should-not-run');
    });

    // Confirm that guests are redirected away from the admin area and the fallback never runs.
    expect($response->isRedirect())->toBeTrue();
    expect($response->getTargetUrl())->toBe(url('/'));
});

it('redirects authenticated members lacking admin permission', function (): void {
    // Fabricate a standard member without admin privileges and authenticate them.
    $member = User::factory()->create([
        'role' => 'user',
    ]);
    actingAs($member);

    // Run the middleware for the authenticated member and intercept the generated response.
    $response = (new CheckAdmin())->handle(Request::create('/admin/dashboard', 'GET'), function (): Response {
        return response('should-not-run');
    });

    // Ensure non-admin members are redirected before reaching the protected action.
    expect($response->isRedirect())->toBeTrue();
    expect($response->getTargetUrl())->toBe(url('/'));
});

it('allows administrators to continue through the middleware stack', function (): void {
    // Authenticate an administrator who carries the wildcard permission set.
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);
    actingAs($admin);

    $nextInvoked = false;

    // Run the middleware while capturing whether the downstream closure executes.
    $response = (new CheckAdmin())->handle(Request::create('/admin/dashboard', 'GET'), function () use (&$nextInvoked): Response {
        $nextInvoked = true;

        return response('middleware-passed');
    });

    // Verify the pipeline continues and the original response bubbles back untouched.
    expect($nextInvoked)->toBeTrue();
    expect($response->getContent())->toBe('middleware-passed');

    // Clear the authenticated user to avoid leaking state between tests without firing logout events.
    Auth::forgetGuards();
});
