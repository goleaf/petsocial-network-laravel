<?php

namespace App\Traits;

use App\Models\Friendship;
use App\Models\Pet;
use App\Models\PetFriendship;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

trait FriendshipComponentTrait
{
    use EntityTypeTrait;
    
    /**
     * Get the entity's friends
     *
     * @param string $status
     * @return Collection
     */
    public function getFriends(string $status = 'accepted'): Collection
    {
        $entity = $this->getEntity();
        $friendships = $this->getFriendships($status);
        $friendIds = $this->extractFriendIds($friendships, $entity->id);
        
        return $this->getEntityModel()::whereIn('id', $friendIds)->get();
    }
    
    /**
     * Get the entity's friendships
     *
     * @param string $status
     * @return Collection
     */
    public function getFriendships(string $status = 'accepted'): Collection
    {
        $entity = $this->getEntity();
        $friendshipModel = $this->getFriendshipModel();
        
        if ($this->entityType === 'pet') {
            return $friendshipModel::where(function ($query) use ($entity) {
                $query->where('pet_id', $entity->id)
                    ->orWhere('friend_pet_id', $entity->id);
            })->where('status', $status)->get();
        } else {
            return $friendshipModel::where(function ($query) use ($entity) {
                $query->where('sender_id', $entity->id)
                    ->orWhere('recipient_id', $entity->id);
            })->where('status', $status)->get();
        }
    }
    
    /**
     * Extract friend IDs from friendships
     *
     * @param Collection $friendships
     * @param int $entityId
     * @return array
     */
    protected function extractFriendIds(Collection $friendships, int $entityId): array
    {
        $friendIds = [];
        
        foreach ($friendships as $friendship) {
            if ($this->entityType === 'pet') {
                $friendIds[] = $friendship->pet_id == $entityId
                    ? $friendship->friend_pet_id
                    : $friendship->pet_id;
            } else {
                $friendIds[] = $friendship->sender_id == $entityId
                    ? $friendship->recipient_id
                    : $friendship->sender_id;
            }
        }
        
        return $friendIds;
    }
    
    /**
     * Send a friend request
     *
     * @param int $friendId
     * @return void
     */
    public function sendFriendRequest(int $friendId): void
    {
        $entity = $this->getEntity();
        
        if ($entity instanceof User) {
            $entity->sendFriendRequest($friendId);
        } elseif ($entity instanceof Pet) {
            $entity->sendFriendRequest($friendId);
        }
        
        $this->dispatchBrowserEvent('friend-request-sent');
    }
    
    /**
     * Accept a friend request
     *
     * @param int $friendId
     * @return void
     */
    public function acceptFriendRequest(int $friendId): void
    {
        $entity = $this->getEntity();
        
        if ($entity instanceof User) {
            $entity->acceptFriendRequest($friendId);
        } elseif ($entity instanceof Pet) {
            $entity->acceptFriendRequest($friendId);
        }
        
        $this->dispatchBrowserEvent('friend-request-accepted');
    }
    
    /**
     * Decline a friend request
     *
     * @param int $friendId
     * @return void
     */
    public function declineFriendRequest(int $friendId): void
    {
        $entity = $this->getEntity();
        
        if ($entity instanceof User) {
            $entity->declineFriendRequest($friendId);
        } elseif ($entity instanceof Pet) {
            $entity->declineFriendRequest($friendId);
        }
        
        $this->dispatchBrowserEvent('friend-request-declined');
    }
    
    /**
     * Block a friend
     *
     * @param int $friendId
     * @return void
     */
    public function blockFriend(int $friendId): void
    {
        $entity = $this->getEntity();
        
        if ($entity instanceof User) {
            $entity->blockFriend($friendId);
        } elseif ($entity instanceof Pet) {
            $entity->blockFriend($friendId);
        }
        
        $this->dispatchBrowserEvent('friend-blocked');
    }
    
    /**
     * Get friend suggestions
     *
     * @param int $limit
     * @return Collection
     */
    public function getFriendSuggestions(int $limit = 5): Collection
    {
        $entity = $this->getEntity();
        $cacheKey = "{$this->entityType}_{$entity->id}_friend_suggestions";
        
        return Cache::remember($cacheKey, now()->addHours(6), function () use ($entity, $limit) {
            // Get existing friend IDs
            $friendships = $this->getFriendships('accepted');
            $friendIds = $this->extractFriendIds($friendships, $entity->id);
            
            // Get pending friend IDs
            $pendingFriendships = $this->getFriendships('pending');
            $pendingFriendIds = $this->extractFriendIds($pendingFriendships, $entity->id);
            
            // Get blocked friend IDs
            $blockedFriendships = $this->getFriendships('blocked');
            $blockedFriendIds = $this->extractFriendIds($blockedFriendships, $entity->id);
            
            // Combine all IDs to exclude
            $excludeIds = array_merge([$entity->id], $friendIds, $pendingFriendIds, $blockedFriendIds);
            
            // Get suggestions based on entity type
            if ($this->entityType === 'pet') {
                // For pets, suggest other pets with similar traits
                return Pet::where('id', '!=', $entity->id)
                    ->whereNotIn('id', $excludeIds)
                    ->where(function ($query) use ($entity) {
                        $query->where('type', $entity->type)
                            ->orWhere('breed', $entity->breed)
                            ->orWhere('location', $entity->location);
                    })
                    ->limit($limit)
                    ->get();
            } else {
                // For users, suggest other users with similar interests or location
                return User::where('id', '!=', $entity->id)
                    ->whereNotIn('id', $excludeIds)
                    ->where(function ($query) use ($entity) {
                        if ($entity->location) {
                            $query->orWhere('location', $entity->location);
                        }
                        
                        // Add more criteria as needed
                    })
                    ->limit($limit)
                    ->get();
            }
        });
    }
    
    /**
     * Check if the entity has a pending friend request from another entity
     *
     * @param int $friendId
     * @return bool
     */
    public function hasFriendRequestFrom(int $friendId): bool
    {
        $entity = $this->getEntity();
        
        if ($entity instanceof User) {
            return $entity->hasFriendRequestFrom($friendId);
        } elseif ($entity instanceof Pet) {
            return $entity->hasFriendRequestFrom($friendId);
        }
        
        return false;
    }
    
    /**
     * Check if the entity has sent a friend request to another entity
     *
     * @param int $friendId
     * @return bool
     */
    public function hasSentFriendRequestTo(int $friendId): bool
    {
        $entity = $this->getEntity();
        
        if ($entity instanceof User) {
            return $entity->hasSentFriendRequestTo($friendId);
        } elseif ($entity instanceof Pet) {
            return $entity->hasSentFriendRequestTo($friendId);
        }
        
        return false;
    }
    
    /**
     * Check if entities are friends
     *
     * @param int $friendId
     * @return bool
     */
    public function isFriendWith(int $friendId): bool
    {
        $entity = $this->getEntity();
        
        if ($entity instanceof User) {
            return $entity->isFriendWith($friendId);
        } elseif ($entity instanceof Pet) {
            return $entity->isFriendWith($friendId);
        }
        
        return false;
    }
}
