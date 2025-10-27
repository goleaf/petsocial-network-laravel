<?php

use App\Http\Livewire\Pet\PetProfile;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    // Rebuild the SQLite test schema so Livewire aliases can resolve seeded pets.
    prepareTestDatabase();

    // Override the view paths so the streamlined testing Blade is resolved first.
    $viewFinder = app('view')->getFinder();
    $this->originalViewPaths = $viewFinder->getPaths();
    $viewFinder->setPaths(array_merge([resource_path('views/tests')], $this->originalViewPaths));
});

afterEach(function (): void {
    // Restore the view finder paths before finishing the scenario.
    $viewFinder = app('view')->getFinder();
    $viewFinder->setPaths($this->originalViewPaths ?? $viewFinder->getPaths());
});

it('registers under a Filament-style Livewire alias without breaking rendering', function () {
    // Reset cached data so the alias registration exercises a clean component instance.
    Cache::flush();

    // Create and authenticate the pet owner to mirror panel access in Filament.
    $owner = User::factory()->create();
    $pet = Pet::factory()->for($owner)->create();
    actingAs($owner);

    // Register the component using a Filament namespace prefix just as panels do internally.
    Livewire::component('filament.pet.profile', PetProfile::class);

    // Register any missing route stubs referenced by the Blade template during rendering.
    if (! Route::has('pet.edit')) {
        Route::get('/testing/pets/{pet}/edit', fn () => '')->name('pet.edit');
    }

    // Render through the namespaced alias and confirm the view data remains intact.
    Livewire::test('filament.pet.profile', ['pet' => $pet->id])
        ->assertViewIs('livewire.pet.profile')
        ->assertViewHas('isOwner', true)
        ->assertViewHas('pet', fn ($resolvedPet) => $resolvedPet->is($pet));
});
