<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

trait FriendshipTrait
{
    /**
     * Get friend IDs for the entity
     *
     * @return array
     */
    public function getFriendIds(): array
    {
        $entity = $this->getEntity();
        $prefix = $this->entityType === 'pet' ? 'pet_' : 'user_';
        $cacheKey = "{$prefix}{$this->entityId}_friend_ids";
        
        return Cache::remember($cacheKey, now()->addHours(1), function () use ($entity) {
            $friendshipModel = $this->getFriendshipModel();
            $entityIdField = $this->getEntityIdField();
            $friendIdField = $this->getFriendIdField();
            
            $friendIds = $friendshipModel::where($entityIdField, $this->entityId)
                ->pluck($friendIdField)
                ->toArray();
                
            // For bidirectional friendships, also get where the entity is the friend
            if ($this->entityType === 'pet') {
                $reverseFriendIds = $friendshipModel::where($friendIdField, $this->entityId)
                    ->pluck($entityIdField)
                    ->toArray();
                    
                $friendIds = array_merge($friendIds, $reverseFriendIds);
            }
            
            return array_unique($friendIds);
        });
    }
    
    /**
     * Check if two entities are friends
     *
     * @param int $friendId
     * @return bool
     */
    public function areFriends(int $friendId): bool
    {
        $friendIds = $this->getFriendIds();
        return in_array($friendId, $friendIds);
    }
    
    /**
     * Add a friend
     *
     * @param int $friendId
     * @return void
     */
    public function addFriend(int $friendId): void
    {
        // Check if friendship already exists
        if ($this->areFriends($friendId)) {
            return;
        }
        
        $friendshipModel = $this->getFriendshipModel();
        $entityIdField = $this->getEntityIdField();
        $friendIdField = $this->getFriendIdField();
        
        // Create the friendship
        $friendshipModel::create([
            $entityIdField => $this->entityId,
            $friendIdField => $friendId,
            'status' => 'accepted', // Default status
        ]);
        
        // Clear cache
        $this->clearEntityCache($this->entityId);
        $this->clearEntityCache($friendId);
    }
    
    /**
     * Remove a friend
     *
     * @param int $friendId
     * @return void
     */
    public function removeFriend(int $friendId): void
    {
        $friendshipModel = $this->getFriendshipModel();
        $entityIdField = $this->getEntityIdField();
        $friendIdField = $this->getFriendIdField();
        
        // Use a transaction for better data integrity
        DB::transaction(function() use ($friendshipModel, $entityIdField, $friendIdField, $friendId) {
            // Delete the friendship in both directions for bidirectional relationships
            $query = $friendshipModel::where(function($q) use ($entityIdField, $friendIdField, $friendId) {
                $q->where($entityIdField, $this->entityId)
                  ->where($friendIdField, $friendId);
            });
            
            if ($this->entityType === 'pet') {
                $query->orWhere(function($q) use ($entityIdField, $friendIdField, $friendId) {
                    $q->where($entityIdField, $friendId)
                      ->where($friendIdField, $this->entityId);
                });
            }
            
            $query->delete();
        });
        
        // Clear cache
        $this->clearEntityCache($this->entityId);
        $this->clearEntityCache($friendId);
    }
    
    /**
     * Get mutual friends between this entity and another entity
     *
     * @param int $otherEntityId
     * @return array
     */
    public function getMutualFriendIds(int $otherEntityId): array
    {
        $entityModel = $this->getEntityModel();
        
        // Get the entity and the other entity
        $entity = $entityModel::findOrFail($this->entityId);
        $otherEntity = $entityModel::findOrFail($otherEntityId);
        
        // Get friend IDs for both entities
        $entityFriendIds = $this->getFriendIds();
        
        // Temporarily change entity ID to get other entity's friends
        $originalEntityId = $this->entityId;
        $this->entityId = $otherEntityId;
        $otherEntityFriendIds = $this->getFriendIds();
        $this->entityId = $originalEntityId;
        
        // Find the intersection of the two friend arrays
        return array_values(array_intersect($entityFriendIds, $otherEntityFriendIds));
    }
    
    /**
     * Categorize friends
     *
     * @param array $friendIds
     * @param string|null $category
     * @return void
     */
    public function categorizeFriends(array $friendIds, ?string $category): void
    {
        if (empty($friendIds)) {
            return;
        }
        
        $friendshipModel = $this->getFriendshipModel();
        $entityIdField = $this->getEntityIdField();
        $friendIdField = $this->getFriendIdField();
        
        // Use a transaction for better data integrity
        DB::transaction(function() use ($friendshipModel, $entityIdField, $friendIdField, $friendIds, $category) {
            $query = $friendshipModel::where(function($q) use ($entityIdField, $friendIdField, $friendIds) {
                $q->where($entityIdField, $this->entityId)
                  ->whereIn($friendIdField, $friendIds);
            });
            
            if ($this->entityType === 'pet') {
                $query->orWhere(function($q) use ($entityIdField, $friendIdField, $friendIds) {
                    $q->where($friendIdField, $this->entityId)
                      ->whereIn($entityIdField, $friendIds);
                });
            }
            
            $query->update(['category' => $category]);
        });
        
        // Clear cache for all affected entities
        $this->clearEntityCache($this->entityId);
        foreach ($friendIds as $friendId) {
            $this->clearEntityCache($friendId);
        }
    }
    
    /**
     * Accept a friend request
     *
     * @param int $friendId
     * @return bool
     */
    public function acceptFriend(int $friendId): bool
    {
        $friendshipModel = $this->getFriendshipModel();
        $entityIdField = $this->getEntityIdField();
        $friendIdField = $this->getFriendIdField();
        
        // Find the pending friendship request
        $friendship = $friendshipModel::where(function($query) use ($entityIdField, $friendIdField, $friendId) {
            $query->where($entityIdField, $friendId)
                  ->where($friendIdField, $this->entityId);
        })->where('status', 'pending')->first();
        
        if (!$friendship) {
            return false;
        }
        
        // Accept the friendship
        $friendship->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
        
        // Clear cache
        $this->clearEntityCache($this->entityId);
        $this->clearEntityCache($friendId);
        
        return true;
    }
    
    /**
     * Decline a friend request
     *
     * @param int $friendId
     * @return bool
     */
    public function declineFriend(int $friendId): bool
    {
        $friendshipModel = $this->getFriendshipModel();
        $entityIdField = $this->getEntityIdField();
        $friendIdField = $this->getFriendIdField();
        
        // Find the pending friendship request
        $friendship = $friendshipModel::where(function($query) use ($entityIdField, $friendIdField, $friendId) {
            $query->where($entityIdField, $friendId)
                  ->where($friendIdField, $this->entityId);
        })->where('status', 'pending')->first();
        
        if (!$friendship) {
            return false;
        }
        
        // Decline the friendship
        $friendship->update([
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);
        
        // Clear cache
        $this->clearEntityCache($this->entityId);
        $this->clearEntityCache($friendId);
        
        return true;
    }
    
    /**
     * Cancel a friend request
     *
     * @param int $friendId
     * @return bool
     */
    public function cancelFriendRequest(int $friendId): bool
    {
        $friendshipModel = $this->getFriendshipModel();
        $entityIdField = $this->getEntityIdField();
        $friendIdField = $this->getFriendIdField();
        
        // Find the pending friendship request
        $friendship = $friendshipModel::where(function($query) use ($entityIdField, $friendIdField, $friendId) {
            $query->where($entityIdField, $this->entityId)
                  ->where($friendIdField, $friendId);
        })->where('status', 'pending')->first();
        
        if (!$friendship) {
            return false;
        }
        
        // Delete the friendship request
        $friendship->delete();
        
        // Clear cache
        $this->clearEntityCache($this->entityId);
        $this->clearEntityCache($friendId);
        
        return true;
    }
    
    /**
     * Block an entity
     *
     * @param int $blockId
     * @return bool
     */
    public function blockEntity(int $blockId): bool
    {
        $friendshipModel = $this->getFriendshipModel();
        $entityIdField = $this->getEntityIdField();
        $friendIdField = $this->getFriendIdField();
        
        // Check if there's an existing friendship
        $friendship = $friendshipModel::where(function($query) use ($entityIdField, $friendIdField, $blockId) {
            $query->where(function($q) use ($entityIdField, $friendIdField, $blockId) {
                $q->where($entityIdField, $this->entityId)
                  ->where($friendIdField, $blockId);
            })->orWhere(function($q) use ($entityIdField, $friendIdField, $blockId) {
                $q->where($entityIdField, $blockId)
                  ->where($friendIdField, $this->entityId);
            });
        })->first();
        
        if ($friendship) {
            // Update existing friendship to blocked
            $friendship->update([
                'status' => 'blocked',
                'blocked_at' => now(),
            ]);
        } else {
            // Create a new blocked friendship
            $friendshipModel::create([
                $entityIdField => $this->entityId,
                $friendIdField => $blockId,
                'status' => 'blocked',
                'blocked_at' => now(),
            ]);
        }
        
        // Clear cache
        $this->clearEntityCache($this->entityId);
        $this->clearEntityCache($blockId);
        
        return true;
    }
    
    /**
     * Unblock an entity
     *
     * @param int $unblockId
     * @return bool
     */
    public function unblockEntity(int $unblockId): bool
    {
        $friendshipModel = $this->getFriendshipModel();
        $entityIdField = $this->getEntityIdField();
        $friendIdField = $this->getFriendIdField();
        
        // Find the blocked friendship
        $friendship = $friendshipModel::where(function($query) use ($entityIdField, $friendIdField, $unblockId) {
            $query->where($entityIdField, $this->entityId)
                  ->where($friendIdField, $unblockId);
        })->where('status', 'blocked')->first();
        
        if (!$friendship) {
            return false;
        }
        
        // Delete the blocked friendship
        $friendship->delete();
        
        // Clear cache
        $this->clearEntityCache($this->entityId);
        $this->clearEntityCache($unblockId);
        
        return true;
    }
    
    /**
     * Get friend suggestions based on mutual friends
     *
     * @param int $limit
     * @return array
     */
    public function getFriendSuggestions(int $limit = 10): array
    {
        $entityModel = $this->getEntityModel();
        $friendshipModel = $this->getFriendshipModel();
        $entityIdField = $this->getEntityIdField();
        $friendIdField = $this->getFriendIdField();
        
        // Get current friend IDs
        $friendIds = $this->getFriendIds();
        
        // Get friends of friends
        $suggestedIds = [];
        
        foreach ($friendIds as $friendId) {
            // Temporarily change entity ID to get friend's friends
            $originalEntityId = $this->entityId;
            $this->entityId = $friendId;
            $friendOfFriendIds = $this->getFriendIds();
            $this->entityId = $originalEntityId;
            
            // Add to suggestions if not already a friend and not self
            foreach ($friendOfFriendIds as $suggestedId) {
                if ($suggestedId != $this->entityId && !in_array($suggestedId, $friendIds)) {
                    if (!isset($suggestedIds[$suggestedId])) {
                        $suggestedIds[$suggestedId] = 1;
                    } else {
                        $suggestedIds[$suggestedId]++;
                    }
                }
            }
        }
        
        // Sort by number of mutual friends (descending)
        arsort($suggestedIds);
        
        // Limit results
        return array_slice($suggestedIds, 0, $limit, true);
    }
}
