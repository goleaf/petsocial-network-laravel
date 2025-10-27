<?php

use App\Models\User;

/**
 * HTTP tests confirm the routing and middleware guards remain intact.
 */
it('redirects non administrators away from the analytics dashboard', function () {
    // A standard member should not satisfy the admin middleware guarding the route.
    $member = User::factory()->create(['role' => 'user']);

    $response = $this->actingAs($member)->get('/admin/analytics');

    // CheckAdmin middleware performs a redirect rather than an authorization exception.
    $response->assertRedirect('/');
});

it('allows administrators to access the analytics dashboard', function () {
    // Grant the acting user the administrator role so the guard passes.
    $admin = User::factory()->create(['role' => 'admin']);

    // Disable Vite asset resolution to prevent manifest lookups from failing inside the test environment.
    $this->withoutVite();

    $response = $this->actingAs($admin)->get('/admin/analytics');

    // The Livewire endpoint should render successfully with an HTTP 200 status.
    $response->assertOk();
});
