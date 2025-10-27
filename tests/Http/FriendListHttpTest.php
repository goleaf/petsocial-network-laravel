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
