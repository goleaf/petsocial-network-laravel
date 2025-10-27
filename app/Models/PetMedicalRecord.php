<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PetMedicalRecord extends Model
{
    /**
     * Allow mass assignment for secure fill operations.
     * Using explicit fillable keeps sensitive fields controlled.
     */
    protected $fillable = [
        'pet_id',
        'primary_veterinarian',
        'clinic_name',
        'clinic_contact',
        'insurance_provider',
        'insurance_policy_number',
        'last_checkup_at',
        'known_conditions',
        'medications',
        'allergies',
        'vaccination_status',
        'microchip_id',
        'dietary_notes',
        'emergency_plan',
    ];

    /**
     * Cast date columns to Carbon instances for easy formatting.
     */
    protected $casts = [
        'last_checkup_at' => 'date',
    ];

    /**
     * Medical record belongs to a specific pet.
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    /**
     * A medical record tracks many veterinary visits over time.
     */
    public function visits(): HasMany
    {
        return $this->hasMany(PetMedicalVisit::class, 'medical_record_id')->orderByDesc('visit_date');
    }
}
