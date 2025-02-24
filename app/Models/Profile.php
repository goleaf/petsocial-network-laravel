<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = ['bio', 'avatar', 'user_id', 'location'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
