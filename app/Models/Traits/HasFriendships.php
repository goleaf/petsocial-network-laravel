<?php

namespace App\Models\Traits;

use App\Models\Friendship;
use App\Models\PetFriendship;
use App\Models\User;
use App\Models\Pet;

trait HasFriendships
{
    /**
     * Get all friendships for this model
     */
    public function friendships()
    {
        if ($this instanceof User) {
            return $this->hasMany(Friendship::class, 'sender_id')
                ->orWhere('recipient_id', $this->id);
        } elseif ($this instanceof Pet) {
            return $this->hasMany(PetFriendship::class, 'pet_id')
                ->orWhere('friend_pet_id', $this->id);
        }
    }

    /**
     * Get all friends for this model
     */
    public function friends()
    {
        if ($this instanceof User) {
            $sentFriendships = Friendship::where('sender_id', $this->id)
                ->where('status', 'accepted')
                ->pluck('recipient_id');

            $receivedFriendships = Friendship::where('recipient_id', $this->id)
                ->where('status', 'accepted')
                ->pluck('sender_id');

            $friendIds = $sentFriendships->merge($receivedFriendships);

            return User::whereIn('id', $friendIds)->get();
        } elseif ($this instanceof Pet) {
            $friendships = PetFriendship::where('pet_id', $this->id)
                ->where('status', 'accepted')
                ->pluck('friend_pet_id');

            $reverseFriendships = PetFriendship::where('friend_pet_id', $this->id)
                ->where('status', 'accepted')
                ->pluck('pet_id');

            $friendIds = $friendships->merge($reverseFriendships);

            return Pet::whereIn('id', $friendIds)->get();
        }
    }

    /**
     * Check if this model is friends with another model
     */
    public function isFriendsWith($model)
    {
        if ($this instanceof User && $model instanceof User) {
            return Friendship::where(function ($query) use ($model) {
                $query->where('sender_id', $this->id)
                    ->where('recipient_id', $model->id);
            })->orWhere(function ($query) use ($model) {
                $query->where('sender_id', $model->id)
                    ->where('recipient_id', $this->id);
            })->where('status', 'accepted')->exists();
        } elseif ($this instanceof Pet && $model instanceof Pet) {
            return PetFriendship::where(function ($query) use ($model) {
                $query->where('pet_id', $this->id)
                    ->where('friend_pet_id', $model->id);
            })->orWhere(function ($query) use ($model) {
                $query->where('pet_id', $model->id)
                    ->where('friend_pet_id', $this->id);
            })->where('status', 'accepted')->exists();
        }
        
        return false;
    }
}
