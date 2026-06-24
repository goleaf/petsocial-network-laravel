<?php

use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests to the login page before loading the admin dashboard', function () {
    // Guests should be challenged by the auth middleware guarding the admin area.
    $response = get(route('admin.dashboard'));
    $response->assertRedirect(route('login'));
});

it('redirects non administrators away from the dashboard entry point', function () {
    // Authenticate as a standard member lacking the admin permission.
    $member = User::withoutEvents(fn () => User::factory()->create());
    actingAs($member);

    // The dedicated middleware should bounce the request back to the home page.
    $response = get(route('admin.dashboard'));
    $response->assertRedirect('/');
});

it('allows administrators to view the dashboard over HTTP', function () {
    // Provision an administrator who satisfies the gate enforced by the middleware stack.
    $admin = User::withoutEvents(fn () => User::factory()->create(['role' => 'admin']));
    actingAs($admin);

    // A successful response should render the Livewire-powered dashboard view.
    $response = get(route('admin.dashboard'));
    $response->assertOk()
        // Check the HTTP layer resolves the Livewire dashboard Blade view for administrators.
        ->assertViewIs('livewire.admin.dashboard');
});
