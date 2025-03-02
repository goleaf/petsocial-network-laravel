<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PetNotification extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'pet_id',
        'sender_pet_id',
        'type',
        'content',
        'data',
        'read_at',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];
    
    /**
     * Get the pet that owns the notification
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }
    
    /**
     * Get the sender pet
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function senderPet(): BelongsTo
    {
        return $this->belongsTo(Pet::class, 'sender_pet_id');
    }
    
    /**
     * Determine if the notification has been read.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function isRead(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->read_at !== null
        );
    }
    
    /**
     * Mark the notification as read.
     *
     * @return void
     */
    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->forceFill(['read_at' => now()])->save();
        }
    }
    
    /**
     * Mark the notification as unread.
     *
     * @return void
     */
    public function markAsUnread()
    {
        if (! is_null($this->read_at)) {
            $this->forceFill(['read_at' => null])->save();
        }
    }
    
    /**
     * Create a new friend request notification
     *
     * @param int $petId
     * @param int $senderPetId
     * @return static
     */
    public static function createFriendRequest(int $petId, int $senderPetId): self
    {
        return self::create([
            'pet_id' => $petId,
            'sender_pet_id' => $senderPetId,
            'type' => 'friend_request',
            'content' => 'sent you a friend request',
            'data' => [
                'action' => 'friend_request',
                'sender_pet_id' => $senderPetId,
            ],
        ]);
    }
    
    /**
     * Create a new friend accept notification
     *
     * @param int $petId
     * @param int $senderPetId
     * @return static
     */
    public static function createFriendAccept(int $petId, int $senderPetId): self
    {
        return self::create([
            'pet_id' => $petId,
            'sender_pet_id' => $senderPetId,
            'type' => 'friend_accept',
            'content' => 'accepted your friend request',
            'data' => [
                'action' => 'friend_accept',
                'sender_pet_id' => $senderPetId,
            ],
        ]);
    }
    
    /**
     * Create a new activity notification
     *
     * @param int $petId
     * @param int $senderPetId
     * @param int $activityId
     * @param string $activityType
     * @return static
     */
    public static function createActivity(int $petId, int $senderPetId, int $activityId, string $activityType): self
    {
        return self::create([
            'pet_id' => $petId,
            'sender_pet_id' => $senderPetId,
            'type' => 'activity',
            'content' => "logged a new {$activityType} activity",
            'data' => [
                'action' => 'activity',
                'sender_pet_id' => $senderPetId,
                'activity_id' => $activityId,
                'activity_type' => $activityType,
            ],
        ]);
    }
}
