<?php

use App\Models\Pet;
use App\Models\User;

/**
 * HTTP layer tests ensuring routing and authorization remain intact.
 */
it('forbids non owners from viewing another pets medical records', function () {
    // Prepare two distinct users so the viewer lacks ownership privileges.
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $pet = Pet::factory()->for($owner)->create();

    $response = $this->actingAs($intruder)->get(route('pet.medical-records', $pet));

    // The authorization check in mount should abort with a 403 response.
    $response->assertForbidden();
});

it('redirects guests to the login page', function () {
    // Create a pet to hit the route without authenticating first.
    $pet = Pet::factory()->create();

    $response = $this->get(route('pet.medical-records', $pet));

    // Auth middleware should bounce unauthenticated visitors to the login form.
    $response->assertRedirect(route('login'));
});

it('renders the Blade template with localized headings', function () {
    // Authenticate as the owner so the Blade view is returned for inspection.
    $owner = User::factory()->create();
    $pet = Pet::factory()->for($owner)->create([
        'name' => 'Rin',
    ]);

    // Disable Vite so asset resolution does not interfere with the response assertions.
    $this->withoutVite();

    $response = $this->actingAs($owner)->get(route('pet.medical-records', $pet));

    // The localized title confirms the Blade template backing the Livewire component rendered successfully.
    $response->assertOk()->assertSee(__('pets.medical_records_title', ['name' => $pet->name]));
});
