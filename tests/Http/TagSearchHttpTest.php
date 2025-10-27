<?php

use App\Http\Livewire\TagSearch;
use App\Models\User;

/**
 * HTTP coverage that ensures the route mounting TagSearch remains reachable.
 */
it('renders the tag search route successfully', function () {
    // Sign in a user because the Livewire component expects an authenticated context.
    $viewer = User::factory()->create();
    $this->actingAs($viewer);

    // Hit the dedicated route and ensure the Livewire component is bootstrapped correctly.
    $response = $this->get(route('tag.search'));

    // Verify the HTTP response is successful and the component placeholder exists in the output.
    $response->assertOk()
        ->assertSeeLivewire(TagSearch::class);
});
