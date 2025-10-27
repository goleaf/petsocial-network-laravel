<?php

use App\Http\Livewire\Pet\MedicalRecords;
use App\Models\Pet;
use App\Models\PetMedicalRecord;
use App\Models\PetMedicalVisit;
use App\Models\User;
use Livewire\Livewire;

/**
 * Livewire specific interaction tests for the pet medical records component.
 */
it('creates a medical record through the Livewire form', function () {
    // Authenticate as the pet owner to satisfy the component guard.
    $owner = User::factory()->create();
    $pet = Pet::factory()->for($owner)->create();

    $this->actingAs($owner);

    Livewire::test(MedicalRecords::class, ['pet' => $pet])
        // Provide a subset of fields to verify mass-assignment and validation succeed.
        ->set('primary_veterinarian', 'Dr. Sasha Lee')
        ->set('clinic_name', 'Northside Veterinary')
        ->call('saveRecord');

    // Confirm the database now contains a persisted record for the pet.
    expect(PetMedicalRecord::where('pet_id', $pet->id)->count())->toBe(1);
});

it('stores a veterinary visit and resets the visit form state', function () {
    // Seed the component with a base medical record to attach visits against.
    $owner = User::factory()->create();
    $pet = Pet::factory()->for($owner)->create();

    $this->actingAs($owner);

    $component = Livewire::test(MedicalRecords::class, ['pet' => $pet])
        ->set('clinic_name', 'Harbor Veterinary')
        ->call('saveRecord');

    $component
        // Populate visit inputs mirroring user supplied details.
        ->set('visit_date', '2024-05-01')
        ->set('visit_veterinarian', 'Dr. Quincy Lake')
        ->set('visit_reason', 'Routine checkup')
        ->call('saveVisit')
        ->assertSet('visit_date', null)
        ->assertSet('visit_veterinarian', null)
        ->assertSet('visit_reason', null)
        ->assertSet('editingVisit', false);

    // The attached visit should exist in the relationship table.
    $record = PetMedicalRecord::first();
    expect($record->visits)->toHaveCount(1);
});

it('loads visit data when edit mode is triggered', function () {
    // Persist an existing visit to confirm edit helpers hydrate the component.
    $owner = User::factory()->create();
    $pet = Pet::factory()->for($owner)->create();
    $record = PetMedicalRecord::create([
        'pet_id' => $pet->id,
    ]);

    $visit = PetMedicalVisit::create([
        'medical_record_id' => $record->id,
        'visit_date' => '2024-04-10',
        'veterinarian' => 'Dr. Jordan Hale',
        'reason' => 'Follow-up',
    ]);

    $this->actingAs($owner);

    Livewire::test(MedicalRecords::class, ['pet' => $pet])
        ->call('editVisit', $visit->id)
        // Editing mode should reflect the stored values and toggle the state flag.
        ->assertSet('visitId', $visit->id)
        ->assertSet('visit_date', '2024-04-10')
        ->assertSet('visit_veterinarian', 'Dr. Jordan Hale')
        ->assertSet('visit_reason', 'Follow-up')
        ->assertSet('editingVisit', true);
});

it('shares the visit collection with the Blade template', function () {
    // Seed the database with a visit so the rendered view has data to expose.
    $owner = User::factory()->create();
    $pet = Pet::factory()->for($owner)->create();
    $record = PetMedicalRecord::create([
        'pet_id' => $pet->id,
    ]);
    $visit = PetMedicalVisit::create([
        'medical_record_id' => $record->id,
        'visit_date' => '2024-06-01',
        'veterinarian' => 'Dr. Riley Fox',
    ]);

    $this->actingAs($owner);

    Livewire::test(MedicalRecords::class, ['pet' => $pet])
        // Ensure the rendered payload exposes the visit collection consumed by the Blade template.
        ->assertViewHas('visits', function ($visits) use ($visit): bool {
            return $visits->contains($visit);
        });
});
