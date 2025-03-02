<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reason',
        'status',
        'notes',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_DISMISSED = 'dismissed';

    /**
     * Get the user who created the report.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who resolved the report.
     */
    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Get the reportable model.
     */
    public function reportable()
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include pending reports.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include resolved reports.
     */
    public function scopeResolved($query)
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    /**
     * Scope a query to only include dismissed reports.
     */
    public function scopeDismissed($query)
    {
        return $query->where('status', self::STATUS_DISMISSED);
    }

    /**
     * Mark the report as resolved.
     */
    public function markAsResolved($userId, $notes = null)
    {
        $this->update([
            'status' => self::STATUS_RESOLVED,
            'resolved_by' => $userId,
            'resolved_at' => now(),
            'notes' => $notes,
        ]);
    }

    /**
     * Mark the report as dismissed.
     */
    public function markAsDismissed($userId, $notes = null)
    {
        $this->update([
            'status' => self::STATUS_DISMISSED,
            'resolved_by' => $userId,
            'resolved_at' => now(),
            'notes' => $notes,
        ]);
    }
}
