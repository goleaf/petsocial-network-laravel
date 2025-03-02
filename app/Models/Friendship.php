<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Friendship extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'recipient_id',
        'status',
        'category',
        'accepted_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    /**
     * Get the sender of the friendship request.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the recipient of the friendship request.
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Scope a query to only include pending friendship requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include accepted friendship requests.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope a query to only include declined friendship requests.
     */
    public function scopeDeclined($query)
    {
        return $query->where('status', 'declined');
    }

    /**
     * Scope a query to only include blocked friendship requests.
     */
    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }

    /**
     * Accept the friendship request.
     */
    public function accept()
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        // Create a notification for the sender
        $this->sender->notifications()->create([
            'type' => 'friendship_accepted',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->recipient_id,
            'data' => [
                'message' => "{$this->recipient->name} accepted your friend request",
                'friendship_id' => $this->id,
            ],
            'priority' => 'normal',
        ]);

        return $this;
    }

    /**
     * Decline the friendship request.
     */
    public function decline()
    {
        $this->update([
            'status' => 'declined',
        ]);

        return $this;
    }

    /**
     * Block the friendship.
     */
    public function block()
    {
        $this->update([
            'status' => 'blocked',
        ]);

        return $this;
    }

    /**
     * Update the friendship category.
     */
    public function categorize($category)
    {
        $this->update([
            'category' => $category,
        ]);

        return $this;
    }
}
