<?php

use App\Http\Middleware\CheckAdmin;
use App\Models\User;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    // Define an HTTP endpoint that simulates an admin API call guarded by the middleware.
    Route::middleware(['web', CheckAdmin::class])->post('/testing/admin-api', function () {
        return response()->json(['status' => 'ok']);
    });
});

/**
 * HTTP coverage validates JSON responses and status codes when the middleware triggers redirects.
 */
it('redirects json requests from guests back to the home page', function (): void {
    // Submit the POST request without authentication to mimic an anonymous API call.
    $response = $this->post('/testing/admin-api');

    // Because the middleware issues a redirect, assert both the status code and destination.
    $response->assertRedirect('/');
});

it('redirects authenticated non-admin json requests', function (): void {
    // Authenticate a member lacking admin privileges.
    $member = User::factory()->create([
        'role' => 'user',
    ]);
    actingAs($member);

    // Attempt the POST request as the regular member.
    $response = $this->post('/testing/admin-api');

    // Validate the redirect behaviour mirrors the guest scenario.
    $response->assertRedirect('/');
});

it('returns the json payload when the requester is an administrator', function (): void {
    // Authenticate an administrator to satisfy the middleware guard.
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);
    actingAs($admin);

    // Perform the POST request as the admin.
    $response = $this->post('/testing/admin-api');

    // Confirm the middleware allowed the request through and returned the JSON payload.
    $response->assertOk();
    $response->assertJson(['status' => 'ok']);
});
