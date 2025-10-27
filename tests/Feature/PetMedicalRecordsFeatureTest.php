<?php

use App\Http\Livewire\Pet\MedicalRecords;
use App\Models\Pet;
use App\Models\PetMedicalRecord;
use App\Models\User;
use Livewire\Livewire;

/**
 * Feature coverage for the pet medical records Livewire route.
 */
it('allows the pet owner to access the medical records dashboard', function () {
    // Create an owner and pet pair to confirm route level authorization permits access.
    $owner = User::factory()->create();
    $pet = Pet::factory()->for($owner)->create();

    // Disable Vite asset loading during the request to avoid missing manifest errors in tests.
    $this->withoutVite();

    $response = $this->actingAs($owner)->get(route('pet.medical-records', $pet));

    // The owner should reach the page successfully while the Livewire route remains accessible.
    $response->assertOk();
});

it('renders the Livewire medical records component inside the Blade view', function () {
    // Build an authenticated request to the route so the Blade view is actually rendered.
    $owner = User::factory()->create();
    $pet = Pet::factory()->for($owner)->create();

    // Avoid Vite manifest lookups when asserting the HTML payload returned by the route.
    $this->withoutVite();

    $response = $this->actingAs($owner)->get(route('pet.medical-records', $pet));

    // Confirm the response references the Livewire alias which proves the Blade template is wired.
    $response->assertOk()->assertSeeLivewire('pet.medical-records');
});

it('prefills form fields when a medical record already exists', function () {
    // Persist a medical record to ensure mount hydrates Livewire public properties.
    $owner = User::factory()->create();
    $pet = Pet::factory()->for($owner)->create();

    PetMedicalRecord::create([
        'pet_id' => $pet->id,
        'primary_veterinarian' => 'Dr. Maya Lopez',
        'clinic_name' => 'Downtown Animal Clinic',
        'clinic_contact' => '555-1000',
        'insurance_provider' => 'Healthy Pet Co',
        'insurance_policy_number' => 'POL123',
    ]);

    $this->actingAs($owner);

    Livewire::test(MedicalRecords::class, ['pet' => $pet])
        // Each field should mirror the stored database values for editing convenience.
        ->assertSet('primary_veterinarian', 'Dr. Maya Lopez')
        ->assertSet('clinic_name', 'Downtown Animal Clinic')
        ->assertSet('clinic_contact', '555-1000')
        ->assertSet('insurance_provider', 'Healthy Pet Co')
        ->assertSet('insurance_policy_number', 'POL123');
});
