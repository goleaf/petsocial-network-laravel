<?php

use App\Http\Livewire\Pet\PetProfile;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

it('registers under a Filament-style Livewire alias without breaking rendering', function () {
    // Reset cached data so the alias registration exercises a clean component instance.
    Cache::flush();

    // Create and authenticate the pet owner to mirror panel access in Filament.
    $owner = User::factory()->create();
    $pet = Pet::factory()->for($owner)->create();
    actingAs($owner);

    // Register the component using a Filament namespace prefix just as panels do internally.
    Livewire::component('filament.pet.profile', PetProfile::class);

    // Render through the namespaced alias and confirm the view data remains intact.
    Livewire::test('filament.pet.profile', ['petId' => $pet->id])
        ->assertViewIs('livewire.pet.profile')
        ->assertViewHas('isOwner', true)
        ->assertViewHas('pet', fn ($resolvedPet) => $resolvedPet->is($pet));
});
