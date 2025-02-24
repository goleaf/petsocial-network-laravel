<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    protected $fillable = ['user_id', 'name', 'type', 'breed', 'birthdate', 'avatar', 'location'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function friends()
    {
        return $this->belongsToMany(Pet::class, 'pet_friendships', 'pet_id', 'friend_pet_id')
            ->withPivot('category');
    }

    public function friendOf()
    {
        return $this->belongsToMany(Pet::class, 'pet_friendships', 'friend_pet_id', 'pet_id')
            ->withPivot('category');
    }

    public function allFriends()
    {
        return $this->friends->merge($this->friendOf);
    }

}
