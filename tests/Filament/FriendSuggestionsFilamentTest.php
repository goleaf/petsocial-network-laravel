<?php

use App\Http\Livewire\Common\Friend\Suggestions;
use App\Models\Pet;
use App\Models\PetFriendship;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use function Pest\Laravel\actingAs;

/**
 * Filament-oriented assurances around the suggestion payload structure.
 */
describe('Friend suggestions Filament compatibility', function () {
    it('produces id to label pairs ready for Filament select components', function () {
        // Reset the cache before assembling pet relationship data.
        Cache::flush();

        // Create an owner and three pets to form a mutual friendship triangle.
        $owner = User::factory()->create();
        $primaryPet = Pet::factory()->create(['user_id' => $owner->id, 'name' => 'Primary Pet']);
        $mutualPet = Pet::factory()->create(['name' => 'Connector Pet']);
        $candidatePet = Pet::factory()->create(['name' => 'Suggested Pet']);

        // Authenticate the owner to mirror the dashboard access pattern.
        actingAs($owner);

        // Persist accepted pet friendships so the candidate shares a mutual connection with the primary pet.
        PetFriendship::create([
            'pet_id' => $primaryPet->id,
            'friend_pet_id' => $mutualPet->id,
            'status' => PetFriendship::STATUS_ACCEPTED,
            'accepted_at' => now(),
        ]);

        PetFriendship::create([
            'pet_id' => $mutualPet->id,
            'friend_pet_id' => $candidatePet->id,
            'status' => PetFriendship::STATUS_ACCEPTED,
            'accepted_at' => now(),
        ]);

        // Mount the component for the pet context so we can transform the collection into Filament-ready options.
        $component = app(Suggestions::class);
        $component->mount('pet', $primaryPet->id);

        $options = $component->suggestions->mapWithKeys(function (array $suggestion) {
            // Filament form selects expect a simple [id => label] structure for option lists.
            return [$suggestion['entity']->id => $suggestion['entity']->name];
        })->all();

        expect($options)->toBe([$candidatePet->id => $candidatePet->name]);
    });
});
