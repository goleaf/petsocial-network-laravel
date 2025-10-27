<?php

namespace App\Models;

use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class PetFriendship extends AbstractFriendship
{
    protected $fillable = [
        'pet_id',
        'friend_pet_id',
        'category',
        'status',
        'accepted_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    /**
     * Get the pet that initiated the friendship
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    /**
     * Get the pet that received the friendship request
     */
    public function friendPet(): BelongsTo
    {
        return $this->belongsTo(Pet::class, 'friend_pet_id');
    }

    /**
     * Create a notification when a friendship is accepted
     */
    protected function createAcceptNotification(): void
    {
        app(NotificationService::class)->send(
            $this->pet->user,
            __('notifications.pet_friendship_accepted', [
                'friend' => $this->friendPet->name,
                'pet' => $this->pet->name,
            ]),
            [
                'type' => 'pet_friendship_accepted',
                'category' => 'friend_requests',
                'priority' => 'normal',
                'data' => [
                    'pet_friendship_id' => $this->id,
                    'pet_id' => $this->pet->id,
                    'friend_pet_id' => $this->friendPet->id,
                ],
                'action_text' => __('notifications.view_pet_profile'),
                'action_url' => route('pet.profile', $this->pet->id),
                'batch_key' => "pet_friendship_accepted:{$this->pet->id}",
                'sender_id' => $this->friendPet->id,
                'sender_type' => Pet::class,
            ]
        );
    }

    /**
     * Clear friendship-related cache for both pets
     */
    protected function clearFriendshipCache(): void
    {
        Cache::forget("pet_{$this->pet_id}_friend_ids");
        Cache::forget("pet_{$this->friend_pet_id}_friend_ids");
        Cache::forget("pet_{$this->pet_id}_friend_suggestions");
        Cache::forget("pet_{$this->friend_pet_id}_friend_suggestions");
    }
}
