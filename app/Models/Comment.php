<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['content', 'user_id', 'post_id', 'parent_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function reports()
    {
        return $this->hasMany(CommentReport::class);
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

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

}
