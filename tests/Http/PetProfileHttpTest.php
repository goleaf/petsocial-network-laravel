<?php

use App\Models\Pet;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('allows the pet owner to access the profile route', function () {
    // Reset cached state to avoid leaking data between HTTP assertions.
    Cache::flush();

    // Create the owner and a private pet profile to guarantee the auth gate is required.
    $owner = User::factory()->create(['name' => 'Profile Owner']);
    $pet = Pet::factory()->for($owner)->create([
        'name' => 'Scout',
        'is_public' => false,
    ]);

    // Authenticate as the owner and load the dedicated profile route.
    actingAs($owner);
    $response = get(route('pet.profile', $pet));

    // Confirm the response is successful and renders key identifying information.
    $response->assertOk();
    $response->assertSee('Scout');
});

it('denies access to private pets for non-owning users', function () {
    // Flush the cache to isolate this access-control assertion.
    Cache::flush();

    // Create the private pet owner and a secondary user that will be blocked.
    $owner = User::factory()->create();
    $pet = Pet::factory()->for($owner)->create([
        'is_public' => false,
    ]);
    $viewer = User::factory()->create();

    // Act as the unrelated viewer and hit the profile route.
    actingAs($viewer);
    $response = get(route('pet.profile', $pet));

    // Assert that the Livewire route returns the expected 403 response.
    $response->assertForbidden();
});
