<?php

use App\Http\Livewire\Common\Friend\List as FriendListComponent;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

/**
 * Validates the HTTP routes wrapping the Livewire friend list component.
 */
it('allows an authenticated pet owner to load the pet friend list page', function (): void {
    // Create a pet owned by a specific user so we can assert access is granted to the owner.
    $owner = User::factory()->create();
    $pet = Pet::factory()->create([
        'user_id' => $owner->id,
    ]);

    Route::middleware(['web', 'auth'])->get('/testing/pet-friends/{pet}', function (int $petId) {
        return Livewire::mount(FriendListComponent::class, [
            'entityType' => 'pet',
            'entityId' => $petId,
        ]);
    });

    actingAs($owner);

    $response = get('/testing/pet-friends/'.$pet->id);

    $response->assertOk();
});

/**
 * Guests should be redirected away from protected friend list pages.
 */
it('redirects guests attempting to view the pet friend list page', function (): void {
    $pet = Pet::factory()->create();

    Route::middleware(['web', 'auth'])->get('/testing/pet-friends/{pet}', function (int $petId) {
        return Livewire::mount(FriendListComponent::class, [
            'entityType' => 'pet',
            'entityId' => $petId,
        ]);
    });

    $response = get('/testing/pet-friends/'.$pet->id);

    $response->assertRedirect(route('login'));
});

/**
 * Verifies that private user friend lists remain inaccessible to other members over HTTP.
 */
it('forbids access to private user friend lists for non-friends', function (): void {
    // Prepare an owner with a private friend list visibility configuration.
    $owner = User::factory()->create([
        'privacy_settings' => ['friends' => 'private'],
    ]);

    // Create a separate viewer that is neither an admin nor a friend of the owner.
    $viewer = User::factory()->create();

    Route::middleware(['web', 'auth'])->get('/testing/user-friends/{user}', function (int $userId) {
        return Livewire::mount(FriendListComponent::class, [
            'entityType' => 'user',
            'entityId' => $userId,
        ]);
    });

    actingAs($viewer);

    $response = get('/testing/user-friends/'.$owner->id);

    $response->assertForbidden();
});
