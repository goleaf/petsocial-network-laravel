<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AccountRecovery stores audit details for password and account recovery flows.
 */
class AccountRecovery extends Model
{
    use HasFactory;

    /**
     * Attributes that are mass assignable for recovery logging.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'email',
        'status',
        'token_identifier',
        'requested_at',
        'completed_at',
        'ip_address',
        'user_agent',
    ];

    /**
     * Attribute casting for date columns to Carbon instances.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'requested_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Associated user when available for the recovery attempt.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
