<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    // Allow mass-assignment for scheduling metadata in addition to the existing fields.
    protected $fillable = ['content', 'user_id', 'pet_id', 'scheduled_for'];

    // Automatically cast scheduling timestamps to Carbon instances for convenience.
    protected $casts = [
        'scheduled_for' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions()
    {
        return $this->hasMany(Reaction::class);
    }

    public function reports()
    {
        return $this->hasMany(PostReport::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function formattedContent()
    {
        $content = $this->content;
        preg_match_all('/@(\w+)/', $content, $matches);
        foreach ($matches[1] as $username) {
            $user = User::where('name', $username)->first();
            if ($user) {
                $content = str_replace("@$username", "<a href='/profile/{$user->id}'>@$username</a>", $content);
            }
        }

        return $content;
    }

    public function shares()
    {
        return $this->hasMany(Share::class);
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }
}
