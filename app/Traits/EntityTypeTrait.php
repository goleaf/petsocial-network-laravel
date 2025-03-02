<?php

namespace App\Traits;

use App\Models\Pet;
use App\Models\User;
use App\Models\PetFriendship;
use App\Models\Friendship;
use Illuminate\Database\Eloquent\Model;

trait EntityTypeTrait
{
    /**
     * The entity type (pet or user)
     *
     * @var string
     */
    public $entityType;
    
    /**
     * The ID of the entity
     *
     * @var int
     */
    public $entityId;
    
    /**
     * Initialize the entity type and ID
     *
     * @param string $entityType
     * @param int $entityId
     * @return void
     */
    public function initializeEntity(string $entityType, int $entityId): void
    {
        if (!in_array($entityType, ['pet', 'user'])) {
            throw new \InvalidArgumentException("Entity type must be 'pet' or 'user'");
        }
        
        $this->entityType = $entityType;
        $this->entityId = $entityId;
    }
    
    /**
     * Get the entity model
     *
     * @return Model
     */
    public function getEntity(): Model
    {
        if ($this->entityType === 'pet') {
            return Pet::findOrFail($this->entityId);
        } else {
            return User::findOrFail($this->entityId);
        }
    }
    
    /**
     * Get the friendship model class name
     *
     * @return string
     */
    public function getFriendshipModel(): string
    {
        return $this->entityType === 'pet' ? PetFriendship::class : Friendship::class;
    }
    
    /**
     * Get the entity model class name
     *
     * @return string
     */
    public function getEntityModel(): string
    {
        return $this->entityType === 'pet' ? Pet::class : User::class;
    }
    
    /**
     * Check if the current user is authorized to manage this entity
     *
     * @return bool
     */
    public function isAuthorized(): bool
    {
        $entity = $this->getEntity();
        
        if ($this->entityType === 'pet') {
            return $entity->user_id === auth()->id();
        } else {
            return $entity->id === auth()->id();
        }
    }
    
    /**
     * Get the ID field name for the friendship relationship
     *
     * @return string
     */
    public function getFriendIdField(): string
    {
        return $this->entityType === 'pet' ? 'friend_pet_id' : 'friend_id';
    }
    
    /**
     * Get the entity ID field name for the friendship relationship
     *
     * @return string
     */
    public function getEntityIdField(): string
    {
        return $this->entityType === 'pet' ? 'pet_id' : 'user_id';
    }
    
    /**
     * Clear entity-related cache
     *
     * @param int|null $entityId
     * @return void
     */
    public function clearEntityCache(?int $entityId = null): void
    {
        $id = $entityId ?? $this->entityId;
        $prefix = $this->entityType === 'pet' ? 'pet_' : 'user_';
        
        \Illuminate\Support\Facades\Cache::forget("{$prefix}{$id}_friend_ids");
        \Illuminate\Support\Facades\Cache::forget("{$prefix}{$id}_friend_count");
    }
}
