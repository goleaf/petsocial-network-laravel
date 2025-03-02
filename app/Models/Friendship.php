<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class Friendship extends AbstractFriendship
{
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
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the recipient of the friendship request.
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
    
    /**
     * Create a notification when a friendship is accepted
     */
    protected function createAcceptNotification(): void
    {
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
    }
    
    /**
     * Clear friendship-related cache for both users
     */
    protected function clearFriendshipCache(): void
    {
        Cache::forget("user_{$this->sender_id}_friend_ids");
        Cache::forget("user_{$this->recipient_id}_friend_ids");
        Cache::forget("user_{$this->sender_id}_friend_suggestions");
        Cache::forget("user_{$this->recipient_id}_friend_suggestions");
    }
}
