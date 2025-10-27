<?php

use App\Http\Livewire\Pet\PetProfile;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\HttpException;
use function Pest\Laravel\actingAs;

it('loads the private pet for its owner when mounted', function () {
    // Reset caches so mount() interacts with a clean storage backend.
    Cache::flush();

    // Create the owner and a private pet record tied to the component under test.
    $owner = User::factory()->create();
    $pet = Pet::factory()->for($owner)->create([
        'is_public' => false,
    ]);

    // Authenticate as the owner to satisfy the authorization gate.
    actingAs($owner);

    // Instantiate the component manually to mirror a unit-level interaction.
    $component = new PetProfile();
    $component->mount($pet->id);

    // Verify the expected state was captured during mounting.
    expect($component->petId)->toBe($pet->id);
    expect($component->pet->is($pet))->toBeTrue();
    expect(Cache::has("pet_profile_{$pet->id}"))->toBeTrue();
});

it('blocks unauthorized viewers from loading private pets', function () {
    // Flush caches to avoid contaminating the authorization assertions.
    Cache::flush();

    // Create the owner, an intruder, and a private pet instance.
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $pet = Pet::factory()->for($owner)->create([
        'is_public' => false,
    ]);

    // Authenticate as the intruder to trigger the authorization guard clause.
    actingAs($intruder);

    // Instantiate the component and assert that mounting raises a 403 exception.
    $component = new PetProfile();
    expect(fn () => $component->mount($pet->id))->toThrow(HttpException::class);
});

it('enforces mutually exclusive visibility toggles for the profile tabs', function () {
    // Create a fresh component instance without relying on the Livewire runtime.
    $component = new PetProfile();

    // Toggling friends should only expose the friends list.
    $component->toggleFriends();
    expect($component->showFriends)->toBeTrue();
    expect($component->showPhotos)->toBeFalse();
    expect($component->showActivities)->toBeFalse();

    // Toggling photos must hide the other panels while exposing the gallery.
    $component->togglePhotos();
    expect($component->showFriends)->toBeFalse();
    expect($component->showPhotos)->toBeTrue();
    expect($component->showActivities)->toBeFalse();

    // Toggling activities should exclusively reveal the activity timeline.
    $component->toggleActivities();
    expect($component->showFriends)->toBeFalse();
    expect($component->showPhotos)->toBeFalse();
    expect($component->showActivities)->toBeTrue();
});
