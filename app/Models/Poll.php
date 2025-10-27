<?php

namespace App\Models;

use App\Models\Group\Topic as GroupTopic;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poll extends AbstractVotableModel
{
    protected $fillable = [
        'question',
        'group_topic_id',
        'user_id',
        'expires_at',
        'allow_multiple',
        'is_anonymous',
        'color',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'allow_multiple' => 'boolean',
        'is_anonymous' => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::created(function ($poll) {
            $poll->clearCache();
        });

        static::updated(function ($poll) {
            $poll->clearCache();
        });

        static::deleted(function ($poll) {
            $poll->clearCache();
        });
    }

    /**
     * Get the group topic this poll belongs to
     */
    public function groupTopic(): BelongsTo
    {
        return $this->belongsTo(GroupTopic::class);
    }

    /**
     * Get the options for this poll
     */
    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class)->orderBy('display_order');
    }

    /**
     * Get the votes for this poll
     */
    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    /**
     * Scope a query to only include active polls
     */
    public function scopeActive($query)
    {
        return $query->where(function ($query) {
            $query->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope a query to only include expired polls
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    /**
     * Cast a vote for this poll
     */
    public function castVote(int $optionId, int $userId): PollVote
    {
        $vote = PollVote::castVote($this->id, $optionId, $userId);
        $this->clearVoteCache();

        return $vote;
    }
}
