<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

// Reset the in-memory SQLite database for each HTTP assertion block.
uses(RefreshDatabase::class);

it('redirects guests attempting to access the unified search route', function (): void {
    // Guests should be routed to the login screen before interacting with the discovery surface.
    $response = $this->get(route('search'));

    $response->assertRedirect(route('login'));
});

it('allows authenticated members to load the unified search Livewire endpoint', function (): void {
    // Clear cached fragments so the component boot logic recomputes sidebar datasets during the request.
    Cache::flush();

    // Authenticate a user to satisfy the route middleware guarding unified search.
    $member = User::factory()->create();
    $this->actingAs($member);

    // Visiting the search route should render the Livewire component without errors.
    $response = $this->get(route('search'));

    $response->assertOk();
    $response->assertSeeLivewire('common.unified-search');
});
