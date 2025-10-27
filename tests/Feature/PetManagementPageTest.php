<?php

use App\Models\Pet;
use App\Models\User;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\withoutVite;

beforeEach(function (): void {
    // Define minimal route stubs so blade links render during the feature assertion.
    if (! Route::has('pet.profile')) {
        Route::name('pet.profile')->get('/testing/pets/{pet}', fn () => '');
    }

    if (! Route::has('pet.friends')) {
        Route::name('pet.friends')->get('/testing/pets/{pet}/friends', fn () => '');
    }

    if (! Route::has('pet.activities')) {
        Route::name('pet.activities')->get('/testing/pets/{pet}/activities', fn () => '');
    }

    if (! Route::has('pet.medical-records')) {
        Route::name('pet.medical-records')->get('/testing/pets/{pet}/medical-records', fn () => '');
    }
});

/**
 * Feature coverage ensuring the pet management dashboard renders expected data.
 */
it('displays the authenticated users pets within the management dashboard', function (): void {
    // Create a member with a couple of pets so the page has content to render.
    $user = User::factory()->create();
    $firstPet = Pet::factory()->for($user)->create(['name' => 'Pixel']);
    $secondPet = Pet::factory()->for($user)->create(['name' => 'Nova']);

    // Authenticate as the owner before requesting the dashboard route.
    actingAs($user);

    // Disable Vite asset loading so the layout renders without the compiled manifest in tests.
    withoutVite();

    // Hit the pets route and confirm the Livewire component and pet names appear.
    get('/pets')
        ->assertOk()
        ->assertSee('Manage Your Pets')
        ->assertSee($firstPet->name)
        ->assertSee($secondPet->name);
});
