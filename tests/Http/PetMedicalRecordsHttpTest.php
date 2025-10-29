<?php

use App\Models\Pet;
use App\Models\User;

beforeEach(function (): void {
    // Initialize the database tables for each request style assertion.
    prepareTestDatabase();
});

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
