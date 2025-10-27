<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
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
        $prefix = $this->entityType === 'pet' ? 'pet_' : 'user_';
        $cacheKey = "{$prefix}{$this->entityId}_friend_ids";
        $friendshipModel = $this->getFriendshipModel();
        $acceptedStatus = $friendshipModel::STATUS_ACCEPTED;

        return Cache::remember($cacheKey, now()->addHours(1), function () use ($friendshipModel, $acceptedStatus) {
            // Retrieve every accepted friendship that involves the current entity and
            // transform the relationship records into a unique list of related IDs.
            $acceptedRelationships = $this->buildRelationshipQuery($friendshipModel)
                ->where('status', $acceptedStatus)
                ->get();

            return $this->extractRelatedEntityIds($acceptedRelationships);
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
        // Get friend IDs for the current entity and the comparison entity, using the
        // cached helper to avoid redundant database queries where possible.
        $entityFriendIds = $this->getFriendIds();

        $originalEntityId = $this->entityId;

        // Temporarily adjust the entity context so that we can reuse the same helper
        // methods without duplicating logic for the counterpart entity.
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

        // Gather relationship IDs by status so we can exclude pending and blocked
        // connections from the suggestion pool.
        $currentFriendIds = $this->getFriendIds();
        $pendingIds = $this->fetchRelatedIdsByStatus($friendshipModel, $friendshipModel::STATUS_PENDING);
        $blockedIds = $this->fetchRelatedIdsByStatus($friendshipModel, $friendshipModel::STATUS_BLOCKED);

        $excludeIds = array_unique(array_merge([
            $this->entityId,
        ], $currentFriendIds, $pendingIds, $blockedIds));

        $candidateMutuals = [];
        $originalEntityId = $this->entityId;

        // Iterate through each accepted friend and look at their accepted friends to
        // discover indirect connections that share mutual friends with the entity.
        foreach ($currentFriendIds as $friendId) {
            $this->entityId = $friendId;
            $friendOfFriendIds = $this->getFriendIds();
            $this->entityId = $originalEntityId;

            foreach ($friendOfFriendIds as $candidateId) {
                if (in_array($candidateId, $excludeIds, true)) {
                    continue;
                }

                // Track the friends responsible for connecting us to the candidate so
                // we can compute mutual friend information later on.
                $candidateMutuals[$candidateId]['mutual_friend_ids'][] = $friendId;
            }
        }

        if (empty($candidateMutuals)) {
            return [];
        }

        $candidateIds = array_keys($candidateMutuals);

        // Retrieve the candidate models in a single query so we can attach them to
        // the suggestion payload without triggering N+1 lookups.
        $candidateEntities = $entityModel::whereIn('id', $candidateIds)
            ->get()
            ->keyBy('id');

        // Collect all mutual friend IDs across every candidate so we can preload the
        // related models and reuse them while formatting the response.
        $allMutualIds = [];
        foreach ($candidateMutuals as $data) {
            $allMutualIds = array_merge($allMutualIds, $data['mutual_friend_ids'] ?? []);
        }

        $mutualFriendEntities = $entityModel::whereIn('id', array_unique($allMutualIds))
            ->get()
            ->keyBy('id');

        // Format the final suggestion list, prioritising candidates with the highest
        // number of mutual friends to satisfy the discovery requirement.
        $suggestions = collect($candidateMutuals)
            ->map(function (array $data, int $candidateId) use ($candidateEntities, $mutualFriendEntities) {
                if (!$candidateEntities->has($candidateId)) {
                    return null;
                }

                $mutualFriendIds = array_values(array_unique($data['mutual_friend_ids'] ?? []));

                if (empty($mutualFriendIds)) {
                    return null;
                }

                $mutualFriends = collect($mutualFriendIds)
                    ->map(function (int $mutualId) use ($mutualFriendEntities) {
                        if (!$mutualFriendEntities->has($mutualId)) {
                            return null;
                        }

                        $friend = $mutualFriendEntities->get($mutualId);

                        return [
                            'id' => $friend->id,
                            'name' => $friend->name,
                            'avatar' => $friend->avatar ?? null,
                        ];
                    })
                    ->filter()
                    ->values()
                    ->toArray();

                $mutualCount = count($mutualFriendIds);

                return [
                    'entity' => $candidateEntities->get($candidateId),
                    'score' => $mutualCount,
                    'mutual_friends_count' => $mutualCount,
                    'mutual_friends' => $mutualFriends,
                ];
            })
            ->filter()
            ->sortByDesc('score')
            ->take($limit)
            ->values()
            ->toArray();

        return $suggestions;
    }

    /**
     * Build a base query for friendships that are associated with the current entity.
     *
     * @param string $friendshipModel
     * @return Builder
     */
    protected function buildRelationshipQuery(string $friendshipModel): Builder
    {
        return $friendshipModel::query()->where(function ($query) {
            if ($this->entityType === 'pet') {
                $query->where('pet_id', $this->entityId)
                    ->orWhere('friend_pet_id', $this->entityId);
            } else {
                $query->where('sender_id', $this->entityId)
                    ->orWhere('recipient_id', $this->entityId);
            }
        });
    }

    /**
     * Convert relationship models into a unique list of counterpart IDs.
     *
     * @param Collection $relationships
     * @return array<int, int>
     */
    protected function extractRelatedEntityIds(Collection $relationships): array
    {
        return $relationships
            ->map(function ($friendship) {
                return $this->resolveCounterpartId($friendship);
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Resolve the ID of the counterpart entity in a friendship relationship.
     *
     * @param Model $friendship
     * @return int|null
     */
    protected function resolveCounterpartId(Model $friendship): ?int
    {
        if ($this->entityType === 'pet') {
            if (isset($friendship->pet_id, $friendship->friend_pet_id)) {
                return (int) ($friendship->pet_id === $this->entityId
                    ? $friendship->friend_pet_id
                    : $friendship->pet_id);
            }
        } else {
            if (isset($friendship->sender_id, $friendship->recipient_id)) {
                return (int) ($friendship->sender_id === $this->entityId
                    ? $friendship->recipient_id
                    : $friendship->sender_id);
            }
        }

        return null;
    }

    /**
     * Fetch counterpart IDs for the given status to simplify exclusion lists.
     *
     * @param string $friendshipModel
     * @param string $status
     * @return array<int, int>
     */
    protected function fetchRelatedIdsByStatus(string $friendshipModel, string $status): array
    {
        $relationships = $this->buildRelationshipQuery($friendshipModel)
            ->where('status', $status)
            ->get();

        return $this->extractRelatedEntityIds($relationships);
    }
}
