<?php

use App\Http\Livewire\Pet\MedicalRecords;
use App\Models\Pet;
use App\Models\PetMedicalRecord;
use Carbon\Carbon;
use Illuminate\View\View;

/**
 * Helper to instantiate an anonymous stub that exposes protected helpers.
 */
function medicalRecordsComponentStub(): MedicalRecords
{
    // Anonymous class keeps Composer from flagging PSR-4 issues while giving tests access.
    return new class extends MedicalRecords
    {
        /**
         * Expose the record validation rules for assertions.
         */
        public function exposeRecordRules(): array
        {
            return $this->recordRules();
        }

        /**
         * Expose the visit validation rules for assertions.
         */
        public function exposeVisitRules(): array
        {
            return $this->visitRules();
        }

        /**
         * Make the protected fill helper callable during tests.
         */
        public function hydrateFromRecord(): void
        {
            $this->fillRecordFields();
        }

        /**
         * Call the original render method so tests can inspect the Blade view binding.
         */
        public function renderForTest(): View
        {
            return $this->render();
        }
    };
}

/**
 * Unit tests confirm validation configuration and field hydration.
 */
it('defines validation rules for the medical record metadata', function () {
    // Instantiate the stub and fetch the validation array for comparison.
    $component = medicalRecordsComponentStub();

    $rules = $component->exposeRecordRules();

    // Each expected key should be present to mirror production safeguards.
    expect($rules)->toHaveKey('primary_veterinarian')
        ->and($rules)->toHaveKey('clinic_name')
        ->and($rules)->toHaveKey('insurance_policy_number')
        ->and($rules)->toHaveKey('emergency_plan');
});

it('defines validation rules for veterinary visit entries', function () {
    // Pull visit specific rules to ensure data sanitation remains enforced.
    $component = medicalRecordsComponentStub();

    $rules = $component->exposeVisitRules();

    expect($rules)->toHaveKey('visit_date')
        ->and($rules)->toHaveKey('visit_veterinarian')
        ->and($rules)->toHaveKey('visit_notes');
});

it('hydrates public properties from the stored medical record', function () {
    // Prepare a hydrated record object mirroring persisted state.
    $component = medicalRecordsComponentStub();

    $component->record = new PetMedicalRecord([
        'primary_veterinarian' => 'Dr. Imani Brooks',
        'clinic_name' => 'Harbor Animal Center',
        'clinic_contact' => '555-8899',
        'insurance_provider' => 'Guardian Pets',
        'insurance_policy_number' => 'GP-42',
        'last_checkup_at' => Carbon::parse('2024-03-15'),
    ]);

    // Call the helper and ensure formatted strings are exposed to Livewire.
    $component->hydrateFromRecord();

    expect($component->primary_veterinarian)->toBe('Dr. Imani Brooks')
        ->and($component->clinic_name)->toBe('Harbor Animal Center')
        ->and($component->clinic_contact)->toBe('555-8899')
        ->and($component->insurance_provider)->toBe('Guardian Pets')
        ->and($component->insurance_policy_number)->toBe('GP-42')
        ->and($component->last_checkup_at)->toBe('2024-03-15');
});

it('renders the expected Blade view with an empty visits collection by default', function () {
    // Provide the minimum component state so render() can resolve the Livewire view.
    $component = medicalRecordsComponentStub();
    $component->pet = new Pet([
        'id' => 1,
        'user_id' => 1,
        'name' => 'Nova',
    ]);
    $component->record = null;

    $view = $component->renderForTest();

    // The render output should point to the Blade template and expose an empty collection of visits.
    expect($view->name())->toBe('livewire.pet.medical-records')
        ->and($view->getData()['visits'])->toEqual(collect());
});
