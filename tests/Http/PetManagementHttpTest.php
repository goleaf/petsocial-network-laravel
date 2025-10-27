<?php

use App\Http\Livewire\Pet\PetManagement;
use App\Models\User;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

/**
 * HTTP surface tests for the pet management Livewire endpoint.
 */
it('redirects guests to the login screen when accessing the pet management dashboard', function (): void {
    // Guests should be bounced to the login page because the route is guarded by the auth middleware.
    get('/pets')->assertRedirect(route('login'));
});

it('allows authenticated users to load the pet management dashboard view', function (): void {
    // Ensure the transient SQLite schema exists before persisting the authenticated member.
    prepareTestDatabase();

    // Authenticate a basic member to confirm the route succeeds when the guard passes.
    $user = User::factory()->create();
    actingAs($user);

    get('/pets')->assertOk()->assertSee('Manage Your Pets');
});

it('registers the pets route with the expected Livewire component and blade view', function (): void {
    // Pull the route definition and verify the Livewire component and view wiring remains intact.
    $route = Route::getRoutes()->getByName('pets');

    expect($route)->not->toBeNull();
    $action = $route->getAction()['uses'] ?? null;
    expect($action)->not->toBeNull();
    expect(str_starts_with($action, PetManagement::class))->toBeTrue();
    expect(view()->exists('livewire.pet.management'))->toBeTrue();
});
