<?php

use App\Models\Pet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\withoutVite;

beforeEach(function (): void {
    // Define the supporting routes referenced by the Blade template when rendering action links.
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
 * Feature level coverage for the pet management Livewire route.
 */
it('renders the pet management dashboard and primes cached pet types', function (): void {
    // Establish the in-memory SQLite schema so model factories can persist records safely.
    prepareTestDatabase();

    // Ensure we start from a clean slate so cache assertions are meaningful.
    Cache::flush();

    // Authenticate an owner with multiple pets spanning different types.
    $user = User::factory()->create();
    actingAs($user);

    $pets = Pet::factory()->count(2)->for($user)->state(new Sequence(
        ['name' => 'Ranger', 'type' => 'dog'],
        ['name' => 'Whiskers', 'type' => 'cat'],
    ))->create();

    // Disable Vite asset loading so the layout renders without needing the compiled manifest during tests.
    withoutVite();

    // Exercise the HTTP entrypoint to confirm the Livewire component renders successfully.
    $response = get('/pets');
    $response->assertOk();
    $response->assertSee('Manage Your Pets');

    // Confirm the rendered HTML includes both pet names so the Blade template is wired correctly.
    foreach ($pets as $pet) {
        $response->assertSee($pet->name);
    }

    // Validate the component populated the cache of available pet types for quick filtering.
    $cachedTypes = Cache::get('user_' . $user->id . '_pet_types');
    expect($cachedTypes)->not->toBeNull();
    expect($cachedTypes->sort()->values()->all())->toEqual(['cat', 'dog']);
});
