<?php

namespace App\Models;

use App\Services\NotificationService;
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
        app(NotificationService::class)->send(
            $this->sender,
            __('notifications.friendship_accepted', ['name' => $this->recipient->name]),
            [
                'type' => 'friendship_accepted',
                'category' => 'friend_requests',
                'priority' => 'normal',
                'data' => [
                    'friendship_id' => $this->id,
                ],
                'action_text' => __('notifications.view_profile'),
                'action_url' => route('profile', $this->recipient),
                'batch_key' => "friendship_accepted:{$this->sender_id}",
                'sender_id' => $this->recipient_id,
                'sender_type' => User::class,
            ]
        );
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
