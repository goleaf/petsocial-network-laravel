<?php

use App\Http\Middleware\CheckAdmin;
use App\Models\User;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    // Register a transient admin route guarded by the CheckAdmin middleware for the scenarios below.
    Route::middleware(['web', CheckAdmin::class])->get('/testing/admin-area', function () {
        return 'admin-zone';
    })->name('testing.admin');
});

/**
 * Feature coverage ensures the middleware integrates properly within the routing layer.
 */
it('redirects guests away from the admin test route', function (): void {
    // Issue a GET request as an unauthenticated visitor.
    $response = $this->get('/testing/admin-area');

    // Confirm the visitor lands back on the home page rather than the protected route content.
    $response->assertRedirect('/');
});

it('redirects authenticated members without admin access', function (): void {
    // Authenticate a regular user lacking the elevated permission set.
    $member = User::factory()->create([
        'role' => 'user',
    ]);
    actingAs($member);

    // Exercise the route while signed in as the standard member.
    $response = $this->get('/testing/admin-area');

    // Validate that even authenticated members are returned to the home page when lacking access.
    $response->assertRedirect('/');
});

it('allows administrators to reach the protected admin test route', function (): void {
    // Authenticate an administrator so the request should clear the middleware stack.
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);
    actingAs($admin);

    // Perform the request and capture the full response for assertions.
    $response = $this->get('/testing/admin-area');

    // Ensure the response succeeds and exposes the protected payload.
    $response->assertOk();
    $response->assertSee('admin-zone');
});
