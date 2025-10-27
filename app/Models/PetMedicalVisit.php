<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PetMedicalVisit extends Model
{
    /**
     * Guarded columns remain empty to simplify updates via fill.
     */
    protected $fillable = [
        'medical_record_id',
        'visit_date',
        'veterinarian',
        'reason',
        'diagnosis',
        'treatment',
        'medications_prescribed',
        'follow_up_date',
        'notes',
    ];

    /**
     * Cast relevant dates to Carbon for consistent formatting.
     */
    protected $casts = [
        'visit_date' => 'date',
        'follow_up_date' => 'date',
    ];

    /**
     * Each visit belongs to a single medical record entry.
     */
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(PetMedicalRecord::class, 'medical_record_id');
    }
}
