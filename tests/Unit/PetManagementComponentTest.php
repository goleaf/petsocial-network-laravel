<?php

use App\Http\Livewire\Pet\PetManagement;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\actingAs;

/**
 * Unit level assurances for helper methods on the pet management component.
 */
it('exposes expected validation rules for pet creation and updates', function (): void {
    // Bootstrap a component instance so we can introspect its protected rules definition.
    $component = new PetManagement();
    $rules = (function (): array {
        return $this->rules();
    })->call($component);

    // Confirm a few representative validation rules to guard against accidental regressions.
    expect($rules['name'])->toBe('required|string|max:255');
    expect($rules['avatar'])->toBe('nullable|image|max:2048');
    expect($rules['favorite_food'])->toBe('nullable|string|max:100');
});

it('clears cached pet data when requested', function (): void {
    // Establish the tables required for authentication before touching the cache helper.
    prepareTestDatabase();

    // Authenticate a user because the cache clearing helper references the current account.
    $user = User::factory()->create();
    actingAs($user);

    // Populate cache entries that should be removed by the helper.
    $petId = 42;
    Cache::put("pet_{$petId}_friend_ids", collect(['friend']));
    Cache::put("pet_{$petId}_recent_activities_5", collect(['activity']));
    Cache::put('user_'.$user->id.'_pet_types', collect(['dog']));

    // Execute the private helper through a bound closure to keep visibility intact while testing.
    $component = new PetManagement();
    (function () use ($petId): void {
        $this->clearPetCache($petId);
    })->call($component);

    // Ensure each cache entry has been removed as expected.
    expect(Cache::has("pet_{$petId}_friend_ids"))->toBeFalse();
    expect(Cache::has("pet_{$petId}_recent_activities_5"))->toBeFalse();
    expect(Cache::has('user_'.$user->id.'_pet_types'))->toBeFalse();
});
