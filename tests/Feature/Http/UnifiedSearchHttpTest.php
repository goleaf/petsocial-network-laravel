<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

/**
 * HTTP-level coverage for the unified search entry point.
 */
it('renders the unified search experience for authenticated members', function () {
    // Clear cached fragments so the Livewire endpoint composes a fresh response.
    Cache::flush();

    // Authenticate a member with standard discovery permissions.
    $member = User::factory()->create([
        'profile_visibility' => 'public',
        'posts_visibility' => 'public',
        'location' => 'Austin',
    ]);
    actingAs($member);

    // Suppress Vite asset resolution so the HTTP layer does not require compiled manifests.
    $this->withoutVite();

    // Issue a GET request against the dedicated search route and assert the response loads.
    get(route('search'))
        ->assertOk()
        ->assertSee('Trending Tags');
});
