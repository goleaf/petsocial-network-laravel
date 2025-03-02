<?php

namespace App\Traits;

use App\Models\Friendship;
use App\Models\PetFriendship;
use App\Models\User;
use App\Models\Pet;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

trait HasFriendships
{
    /**
     * Get all friendships for this model
     *
     * @return HasMany
     */
    public function friendships(): HasMany
    {
        if ($this instanceof User) {
            return $this->hasMany(Friendship::class, 'sender_id')
                ->orWhere('recipient_id', $this->id);
        } elseif ($this instanceof Pet) {
            return $this->hasMany(PetFriendship::class, 'pet_id')
                ->orWhere('friend_pet_id', $this->id);
        }
        
        throw new \Exception('Model must be a User or Pet to use HasFriendships trait');
    }
    
    /**
     * Get all accepted friendships
     *
     * @return Collection
     */
    public function getAcceptedFriendships(): Collection
    {
        $cacheKey = $this->getFriendshipCacheKey('accepted');
        
        return Cache::remember($cacheKey, now()->addHours(24), function () {
            if ($this instanceof User) {
                return Friendship::where(function ($query) {
                    $query->where('sender_id', $this->id)
                        ->orWhere('recipient_id', $this->id);
                })->accepted()->get();
            } elseif ($this instanceof Pet) {
                return PetFriendship::where(function ($query) {
                    $query->where('pet_id', $this->id)
                        ->orWhere('friend_pet_id', $this->id);
                })->accepted()->get();
            }
            
            return collect();
        });
    }
    
    /**
     * Get all pending friendships
     *
     * @return Collection
     */
    public function getPendingFriendships(): Collection
    {
        $cacheKey = $this->getFriendshipCacheKey('pending');
        
        return Cache::remember($cacheKey, now()->addHours(6), function () {
            if ($this instanceof User) {
                return Friendship::where(function ($query) {
                    $query->where('sender_id', $this->id)
                        ->orWhere('recipient_id', $this->id);
                })->pending()->get();
            } elseif ($this instanceof Pet) {
                return PetFriendship::where(function ($query) {
                    $query->where('pet_id', $this->id)
                        ->orWhere('friend_pet_id', $this->id);
                })->pending()->get();
            }
            
            return collect();
        });
    }
    
    /**
     * Get all blocked friendships
     *
     * @return Collection
     */
    public function getBlockedFriendships(): Collection
    {
        $cacheKey = $this->getFriendshipCacheKey('blocked');
        
        return Cache::remember($cacheKey, now()->addHours(24), function () {
            if ($this instanceof User) {
                return Friendship::where(function ($query) {
                    $query->where('sender_id', $this->id)
                        ->orWhere('recipient_id', $this->id);
                })->blocked()->get();
            } elseif ($this instanceof Pet) {
                return PetFriendship::where(function ($query) {
                    $query->where('pet_id', $this->id)
                        ->orWhere('friend_pet_id', $this->id);
                })->blocked()->get();
            }
            
            return collect();
        });
    }
    
    /**
     * Send a friend request
     *
     * @param int $friendId
     * @return void
     */
    public function sendFriendRequest(int $friendId): void
    {
        if ($this->hasFriendRequestFrom($friendId) || $this->hasSentFriendRequestTo($friendId)) {
            return;
        }
        
        if ($this instanceof User) {
            Friendship::create([
                'sender_id' => $this->id,
                'recipient_id' => $friendId,
                'status' => Friendship::STATUS_PENDING,
            ]);
        } elseif ($this instanceof Pet) {
            PetFriendship::create([
                'pet_id' => $this->id,
                'friend_pet_id' => $friendId,
                'status' => PetFriendship::STATUS_PENDING,
            ]);
        }
        
        $this->clearFriendshipCache();
    }
    
    /**
     * Accept a friend request
     *
     * @param int $friendId
     * @return void
     */
    public function acceptFriendRequest(int $friendId): void
    {
        if (!$this->hasFriendRequestFrom($friendId)) {
            return;
        }
        
        if ($this instanceof User) {
            $friendship = Friendship::where('sender_id', $friendId)
                ->where('recipient_id', $this->id)
                ->pending()
                ->first();
                
            if ($friendship) {
                $friendship->accept();
            }
        } elseif ($this instanceof Pet) {
            $friendship = PetFriendship::where('pet_id', $friendId)
                ->where('friend_pet_id', $this->id)
                ->pending()
                ->first();
                
            if ($friendship) {
                $friendship->accept();
            }
        }
        
        $this->clearFriendshipCache();
    }
    
    /**
     * Decline a friend request
     *
     * @param int $friendId
     * @return void
     */
    public function declineFriendRequest(int $friendId): void
    {
        if (!$this->hasFriendRequestFrom($friendId)) {
            return;
        }
        
        if ($this instanceof User) {
            $friendship = Friendship::where('sender_id', $friendId)
                ->where('recipient_id', $this->id)
                ->pending()
                ->first();
                
            if ($friendship) {
                $friendship->decline();
            }
        } elseif ($this instanceof Pet) {
            $friendship = PetFriendship::where('pet_id', $friendId)
                ->where('friend_pet_id', $this->id)
                ->pending()
                ->first();
                
            if ($friendship) {
                $friendship->decline();
            }
        }
        
        $this->clearFriendshipCache();
    }
    
    /**
     * Block a friend
     *
     * @param int $friendId
     * @return void
     */
    public function blockFriend(int $friendId): void
    {
        if ($this instanceof User) {
            $friendship = Friendship::where(function ($query) use ($friendId) {
                $query->where('sender_id', $this->id)
                    ->where('recipient_id', $friendId);
            })->orWhere(function ($query) use ($friendId) {
                $query->where('sender_id', $friendId)
                    ->where('recipient_id', $this->id);
            })->first();
            
            if (!$friendship) {
                $friendship = Friendship::create([
                    'sender_id' => $this->id,
                    'recipient_id' => $friendId,
                    'status' => Friendship::STATUS_BLOCKED,
                ]);
            } else {
                $friendship->block();
            }
        } elseif ($this instanceof Pet) {
            $friendship = PetFriendship::where(function ($query) use ($friendId) {
                $query->where('pet_id', $this->id)
                    ->where('friend_pet_id', $friendId);
            })->orWhere(function ($query) use ($friendId) {
                $query->where('pet_id', $friendId)
                    ->where('friend_pet_id', $this->id);
            })->first();
            
            if (!$friendship) {
                $friendship = PetFriendship::create([
                    'pet_id' => $this->id,
                    'friend_pet_id' => $friendId,
                    'status' => PetFriendship::STATUS_BLOCKED,
                ]);
            } else {
                $friendship->block();
            }
        }
        
        $this->clearFriendshipCache();
    }
    
    /**
     * Check if model has a friend request from another model
     *
     * @param int $friendId
     * @return bool
     */
    public function hasFriendRequestFrom(int $friendId): bool
    {
        if ($this instanceof User) {
            return Friendship::where('sender_id', $friendId)
                ->where('recipient_id', $this->id)
                ->pending()
                ->exists();
        } elseif ($this instanceof Pet) {
            return PetFriendship::where('pet_id', $friendId)
                ->where('friend_pet_id', $this->id)
                ->pending()
                ->exists();
        }
        
        return false;
    }
    
    /**
     * Check if model has sent a friend request to another model
     *
     * @param int $friendId
     * @return bool
     */
    public function hasSentFriendRequestTo(int $friendId): bool
    {
        if ($this instanceof User) {
            return Friendship::where('sender_id', $this->id)
                ->where('recipient_id', $friendId)
                ->pending()
                ->exists();
        } elseif ($this instanceof Pet) {
            return PetFriendship::where('pet_id', $this->id)
                ->where('friend_pet_id', $friendId)
                ->pending()
                ->exists();
        }
        
        return false;
    }
    
    /**
     * Check if models are friends
     *
     * @param int $friendId
     * @return bool
     */
    public function isFriendWith(int $friendId): bool
    {
        if ($this instanceof User) {
            return Friendship::where(function ($query) use ($friendId) {
                $query->where('sender_id', $this->id)
                    ->where('recipient_id', $friendId);
            })->orWhere(function ($query) use ($friendId) {
                $query->where('sender_id', $friendId)
                    ->where('recipient_id', $this->id);
            })->accepted()->exists();
        } elseif ($this instanceof Pet) {
            return PetFriendship::where(function ($query) use ($friendId) {
                $query->where('pet_id', $this->id)
                    ->where('friend_pet_id', $friendId);
            })->orWhere(function ($query) use ($friendId) {
                $query->where('pet_id', $friendId)
                    ->where('friend_pet_id', $this->id);
            })->accepted()->exists();
        }
        
        return false;
    }
    
    /**
     * Get friendship cache key
     *
     * @param string $type
     * @return string
     */
    protected function getFriendshipCacheKey(string $type): string
    {
        $modelType = $this instanceof User ? 'user' : 'pet';
        return "{$modelType}_{$this->id}_friendships_{$type}";
    }
    
    /**
     * Clear friendship cache
     *
     * @return void
     */
    protected function clearFriendshipCache(): void
    {
        $modelType = $this instanceof User ? 'user' : 'pet';
        Cache::forget("{$modelType}_{$this->id}_friendships_accepted");
        Cache::forget("{$modelType}_{$this->id}_friendships_pending");
        Cache::forget("{$modelType}_{$this->id}_friendships_blocked");
        Cache::forget("{$modelType}_{$this->id}_friend_ids");
        Cache::forget("{$modelType}_{$this->id}_friend_suggestions");
    }
}
