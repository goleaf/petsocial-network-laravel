<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostReport extends Model
{
    protected $fillable = ['user_id', 'post_id', 'reason'];

    /**
     * Register model event hooks that keep moderation automation in sync.
     */
    protected static function booted(): void
    {
        static::created(function (PostReport $report): void {
            optional($report->post?->user)->evaluateAutomatedModeration();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
