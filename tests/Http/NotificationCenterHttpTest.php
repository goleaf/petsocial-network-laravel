<?php

use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

// Refresh the database so HTTP assertions interact with fresh notification tables on every run.
uses(RefreshDatabase::class);

it('denies access to pet notifications for non owners', function (): void {
    // Flush caches to avoid previously computed unread counts influencing the abort path.
    Cache::flush();

    // Prepare a pet owned by one user while authenticating a different member to simulate the forbidden state.
    $owner = User::factory()->create();
    $pet = Pet::factory()->for($owner)->create();
    $stranger = User::factory()->create();

    // Request the pet notification center as a non-owner and ensure the component aborts with a forbidden response.
    $this->actingAs($stranger)
        ->get(route('pet.notifications', $pet->id))
        ->assertForbidden();
});

it('allows owners to load the pet notification center view', function (): void {
    // Reset any notification caches so the component can recalculate the unread count for the owner.
    Cache::flush();

    // Authenticate as the pet owner to confirm the route resolves successfully.
    $owner = User::factory()->create();
    $pet = Pet::factory()->for($owner)->create();

    $this->actingAs($owner)
        ->get(route('pet.notifications', $pet->id))
        ->assertOk();
});
