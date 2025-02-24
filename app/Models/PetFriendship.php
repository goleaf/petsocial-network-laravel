<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetFriendship extends Model
{
    protected $fillable = ['pet_id', 'friend_pet_id', 'category'];

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function friendPet()
    {
        return $this->belongsTo(Pet::class, 'friend_pet_id');
    }
}
