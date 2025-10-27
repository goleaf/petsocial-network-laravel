<?php

use App\Models\User;

/**
 * HTTP tests confirm the web routes exposing the Livewire component behave correctly.
 */
it('redirects guests away from the settings dashboard', function () {
    // Guests should be redirected to the login page when attempting to view settings.
    $response = $this->get('/settings');

    $response->assertRedirect(route('login'));
});

it('renders the user settings Livewire component for authenticated members', function () {
    $user = User::factory()->create([
        'profile_visibility' => 'public',
        'posts_visibility' => 'public',
    ]);

    // Skip Vite asset resolution to prevent missing manifest errors during the request cycle.
    $this->withoutVite();

    // Authenticate and request the settings route to ensure the component loads.
    $response = $this->actingAs($user)->get('/settings');

    $response->assertOk();
    // Ensure the layout renders alongside Livewire sidebars to confirm the page booted correctly.
    $response->assertSee('Trending Tags');

    // The dedicated Livewire tests verify component internals; this check focuses on HTTP reachability.
});
