<?php

namespace App\Models;

use App\Models\Merged\Comment as DiscussionComment;
use Illuminate\Database\Eloquent\Model;

class CommentReport extends Model
{
    protected $fillable = ['user_id', 'comment_id', 'reason'];

    /**
     * Keep moderation automation responsive when new reports arrive.
     */
    protected static function booted(): void
    {
        static::created(function (CommentReport $report): void {
            optional($report->comment?->user)->evaluateAutomatedModeration();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comment()
    {
        return $this->belongsTo(DiscussionComment::class, 'comment_id');
    }
}
