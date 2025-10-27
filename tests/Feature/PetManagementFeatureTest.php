<?php

use App\Http\Livewire\Pet\PetManagement;
use App\Models\Pet;
use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('renders the pet management dashboard with the expected blade view', function (): void {
    // Authenticate an owner so the /pets route is accessible during the feature assertion.
    $user = User::factory()->create();
    actingAs($user);

    // Seed a pair of pets so the management table has content to render and link against.
    $pets = Pet::factory()->count(2)->for($user)->sequence(
        ['name' => 'Nova', 'type' => 'Dog'],
        ['name' => 'Pixel', 'type' => 'Cat']
    )->create();

    // Hit the dedicated pet management route and capture the rendered response for verification.
    $response = get(route('pets'));

    // Confirm the Livewire component is bootstrapped and the expected copy from the blade is present.
    $response->assertOk();
    $response->assertSee('Manage Your Pets');
    $response->assertSeeLivewire(PetManagement::class);

    // Ensure the blade template is available and wiring the medical records link correctly for each pet.
    expect(view()->exists('livewire.pet.management'))->toBeTrue();
    $response->assertSee(route('pet.medical-records', $pets->first()), false);
});
