<?php

namespace App\Http\Livewire\Pet;

use App\Models\Pet;
use App\Models\PetMedicalRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class MedicalRecords extends Component
{
    /**
     * The pet that owns the medical records section.
     */
    public Pet $pet;

    /**
     * Cached record instance helps with updates and visit association.
     */
    public ?PetMedicalRecord $record = null;

    /**
     * Fields for the core medical record metadata.
     */
    public $primary_veterinarian;
    public $clinic_name;
    public $clinic_contact;
    public $insurance_provider;
    public $insurance_policy_number;
    public $last_checkup_at;
    public $known_conditions;
    public $medications;
    public $allergies;
    public $vaccination_status;
    public $microchip_id;
    public $dietary_notes;
    public $emergency_plan;

    /**
     * Visit form state for creating or editing veterinary history entries.
     */
    public $visitId;
    public $visit_date;
    public $visit_veterinarian;
    public $visit_reason;
    public $visit_diagnosis;
    public $visit_treatment;
    public $visit_medications_prescribed;
    public $visit_follow_up_date;
    public $visit_notes;

    /**
     * Track when we are editing an existing visit to toggle UI labels.
     */
    public bool $editingVisit = false;

    /**
     * Authorize and bootstrap component state.
     */
    public function mount(Pet $pet): void
    {
        // Ensure only the owner can access the private medical records.
        abort_if($pet->user_id !== Auth::id(), 403, __('pets.medical_records_unauthorized'));

        $this->pet = $pet;
        $this->record = $pet->medicalRecord;

        // Pre-fill form fields when a record already exists for convenience.
        if ($this->record) {
            $this->fillRecordFields();
        }
    }

    /**
     * Validation rules for primary record details.
     */
    protected function recordRules(): array
    {
        return [
            'primary_veterinarian' => ['nullable', 'string', 'max:255'],
            'clinic_name' => ['nullable', 'string', 'max:255'],
            'clinic_contact' => ['nullable', 'string', 'max:255'],
            'insurance_provider' => ['nullable', 'string', 'max:255'],
            'insurance_policy_number' => ['nullable', 'string', 'max:255'],
            'last_checkup_at' => ['nullable', 'date'],
            'known_conditions' => ['nullable', 'string'],
            'medications' => ['nullable', 'string'],
            'allergies' => ['nullable', 'string'],
            'vaccination_status' => ['nullable', 'string'],
            'microchip_id' => ['nullable', 'string', 'max:255'],
            'dietary_notes' => ['nullable', 'string'],
            'emergency_plan' => ['nullable', 'string'],
        ];
    }

    /**
     * Validation rules for veterinary visit entries.
     */
    protected function visitRules(): array
    {
        return [
            'visit_date' => ['nullable', 'date'],
            'visit_veterinarian' => ['nullable', 'string', 'max:255'],
            'visit_reason' => ['nullable', 'string', 'max:255'],
            'visit_diagnosis' => ['nullable', 'string', 'max:255'],
            'visit_treatment' => ['nullable', 'string'],
            'visit_medications_prescribed' => ['nullable', 'string'],
            'visit_follow_up_date' => ['nullable', 'date'],
            'visit_notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Persist record data while ensuring only allowed attributes are saved.
     */
    public function saveRecord(): void
    {
        $validated = $this->validate($this->recordRules());

        try {
            if ($this->record) {
                // Update existing record with new details.
                $this->record->update($validated);
            } else {
                // Create a fresh record and cache it for future interactions.
                $this->record = PetMedicalRecord::create(array_merge($validated, [
                    'pet_id' => $this->pet->id,
                ]));
            }

            session()->flash('medical_record_saved', __('pets.medical_records_saved'));
        } catch (\Throwable $exception) {
            // Log the exception for debugging without leaking details to the user.
            Log::error('Failed to save medical record', [
                'pet_id' => $this->pet->id,
                'error' => $exception->getMessage(),
            ]);

            session()->flash('medical_record_error', __('pets.medical_records_error'));
        }
    }

    /**
     * Reset visit form state after saving or cancelling.
     */
    public function resetVisitForm(): void
    {
        $this->reset([
            'visitId',
            'visit_date',
            'visit_veterinarian',
            'visit_reason',
            'visit_diagnosis',
            'visit_treatment',
            'visit_medications_prescribed',
            'visit_follow_up_date',
            'visit_notes',
            'editingVisit',
        ]);
    }

    /**
     * Persist visit history records while referencing the parent medical record.
     */
    public function saveVisit(): void
    {
        // Ensure the base medical record exists before visits can be stored.
        if (!$this->record) {
            session()->flash('medical_record_error', __('pets.medical_records_create_first'));
            return;
        }

        $validated = $this->validate($this->visitRules());

        try {
            if ($this->editingVisit && $this->visitId) {
                // Update the selected visit entry.
                $visit = $this->record->visits()->findOrFail($this->visitId);
                $visit->update([
                    'visit_date' => $validated['visit_date'] ?? null,
                    'veterinarian' => $validated['visit_veterinarian'] ?? null,
                    'reason' => $validated['visit_reason'] ?? null,
                    'diagnosis' => $validated['visit_diagnosis'] ?? null,
                    'treatment' => $validated['visit_treatment'] ?? null,
                    'medications_prescribed' => $validated['visit_medications_prescribed'] ?? null,
                    'follow_up_date' => $validated['visit_follow_up_date'] ?? null,
                    'notes' => $validated['visit_notes'] ?? null,
                ]);
            } else {
                // Create a new visit history record and attach it to the parent.
                $this->record->visits()->create([
                    'visit_date' => $validated['visit_date'] ?? null,
                    'veterinarian' => $validated['visit_veterinarian'] ?? null,
                    'reason' => $validated['visit_reason'] ?? null,
                    'diagnosis' => $validated['visit_diagnosis'] ?? null,
                    'treatment' => $validated['visit_treatment'] ?? null,
                    'medications_prescribed' => $validated['visit_medications_prescribed'] ?? null,
                    'follow_up_date' => $validated['visit_follow_up_date'] ?? null,
                    'notes' => $validated['visit_notes'] ?? null,
                ]);
            }

            session()->flash('medical_visit_saved', __('pets.medical_visit_saved'));
            $this->resetVisitForm();
            // Refresh record relationship to display the latest data immediately.
            $this->record->refresh();
        } catch (\Throwable $exception) {
            Log::error('Failed to save medical visit', [
                'pet_id' => $this->pet->id,
                'visit_id' => $this->visitId,
                'error' => $exception->getMessage(),
            ]);

            session()->flash('medical_record_error', __('pets.medical_records_error'));
        }
    }

    /**
     * Populate visit form with existing information when editing.
     */
    public function editVisit(int $visitId): void
    {
        if (!$this->record) {
            return;
        }

        $visit = $this->record->visits()->findOrFail($visitId);

        $this->visitId = $visit->id;
        $this->visit_date = optional($visit->visit_date)->format('Y-m-d');
        $this->visit_veterinarian = $visit->veterinarian;
        $this->visit_reason = $visit->reason;
        $this->visit_diagnosis = $visit->diagnosis;
        $this->visit_treatment = $visit->treatment;
        $this->visit_medications_prescribed = $visit->medications_prescribed;
        $this->visit_follow_up_date = optional($visit->follow_up_date)->format('Y-m-d');
        $this->visit_notes = $visit->notes;
        $this->editingVisit = true;
    }

    /**
     * Delete an existing visit entry safely.
     */
    public function deleteVisit(int $visitId): void
    {
        if (!$this->record) {
            return;
        }

        try {
            $this->record->visits()->where('id', $visitId)->delete();
            session()->flash('medical_visit_deleted', __('pets.medical_visit_deleted'));
            $this->record->refresh();
        } catch (\Throwable $exception) {
            Log::error('Failed to delete medical visit', [
                'pet_id' => $this->pet->id,
                'visit_id' => $visitId,
                'error' => $exception->getMessage(),
            ]);

            session()->flash('medical_record_error', __('pets.medical_records_error'));
        }

        // Ensure any form referencing the deleted visit is reset.
        $this->resetVisitForm();
    }

    /**
     * Copy data from the stored record to the public properties.
     */
    protected function fillRecordFields(): void
    {
        $this->primary_veterinarian = $this->record->primary_veterinarian;
        $this->clinic_name = $this->record->clinic_name;
        $this->clinic_contact = $this->record->clinic_contact;
        $this->insurance_provider = $this->record->insurance_provider;
        $this->insurance_policy_number = $this->record->insurance_policy_number;
        $this->last_checkup_at = optional($this->record->last_checkup_at)->format('Y-m-d');
        $this->known_conditions = $this->record->known_conditions;
        $this->medications = $this->record->medications;
        $this->allergies = $this->record->allergies;
        $this->vaccination_status = $this->record->vaccination_status;
        $this->microchip_id = $this->record->microchip_id;
        $this->dietary_notes = $this->record->dietary_notes;
        $this->emergency_plan = $this->record->emergency_plan;
    }

    /**
     * Render the medical records management view.
     */
    public function render()
    {
        return view('livewire.pet.medical-records', [
            'visits' => $this->record?->visits ?? collect(),
        ])->layout('layouts.app');
    }
}
