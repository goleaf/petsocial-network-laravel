<?php

namespace App\Models\Merged;

use App\Models\AbstractModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollVote extends AbstractModel
{
    protected $fillable = [
        'poll_id',
        'poll_option_id',
        'user_id',
    ];
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::created(function ($vote) {
            // Clear related caches when a vote is created
            if ($vote->poll) {
                $vote->poll->clearVoteCache();
            }
            
            if ($vote->option) {
                $vote->option->clearCache();
            }
        });
        
        static::deleted(function ($vote) {
            // Clear related caches when a vote is deleted
            if ($vote->poll) {
                $vote->poll->clearVoteCache();
            }
            
            if ($vote->option) {
                $vote->option->clearCache();
            }
        });
    }

    /**
     * Get the poll this vote belongs to
     */
    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    /**
     * Get the option this vote is for
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(PollOption::class, 'poll_option_id');
    }

    /**
     * Get the user who cast this vote
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a new vote
     *
     * @param int $pollId
     * @param int $optionId
     * @param int $userId
     * @return self
     */
    public static function castVote(int $pollId, int $optionId, int $userId): self
    {
        // Check if user has already voted on this poll if multiple votes aren't allowed
        $poll = Poll::findOrFail($pollId);
        
        if (!$poll->allow_multiple) {
            // Delete any existing votes by this user for this poll
            static::where('poll_id', $pollId)
                  ->where('user_id', $userId)
                  ->delete();
        }
        
        // Create the new vote
        $vote = static::create([
            'poll_id' => $pollId,
            'poll_option_id' => $optionId,
            'user_id' => $userId,
        ]);
        
        // Clear related caches
        $poll->clearVoteCache();
        
        return $vote;
    }
}
