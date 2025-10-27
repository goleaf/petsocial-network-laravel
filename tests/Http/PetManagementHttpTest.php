<?php

use App\Http\Livewire\Pet\PetManagement;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    // Register lightweight route placeholders so blade links resolve inside HTTP assertions.
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

it('redirects guests to the login page when accessing the pet management route', function (): void {
    // Exercise the /pets endpoint as a guest to confirm authentication is enforced by the middleware group.
    $response = get(route('pets'));

    // Laravel should redirect unauthenticated visitors to the login screen for protected routes.
    $response->assertRedirect(route('login'));
});

it('serves the livewire-backed pet management view for authenticated members', function (): void {
    // Authenticate a member with a managed pet so the rendered HTML has tangible data.
    $user = User::factory()->create();
    actingAs($user);
    Pet::factory()->for($user)->create(['name' => 'Scout']);

    // Request the route and capture the HTML to confirm the component wiring and blade output.
    $response = get(route('pets'));

    // Validate the HTTP response includes the expected Livewire component and dashboard copy.
    $response->assertOk();
    $response->assertSee('Manage Your Pets');
    $response->assertSeeLivewire(PetManagement::class);
});
