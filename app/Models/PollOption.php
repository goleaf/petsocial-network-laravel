<?php

namespace App\Models\Merged;

use App\Models\AbstractModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class PollOption extends AbstractModel
{
    protected $fillable = [
        'poll_id',
        'text',
        'color',
        'display_order',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::created(function ($option) {
            $option->clearCache();
            $option->poll->clearVoteCache();
        });
        
        static::updated(function ($option) {
            $option->clearCache();
        });
        
        static::deleted(function ($option) {
            $option->poll->clearVoteCache();
        });
    }

    /**
     * Get the poll this option belongs to
     */
    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    /**
     * Get the votes for this option
     */
    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    /**
     * Get the vote count for this option
     */
    public function getVoteCountAttribute(): int
    {
        $cacheKey = $this->generateCacheKey('vote_count');
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return $this->votes()->count();
        });
    }

    /**
     * Get the percentage of votes for this option
     */
    public function getPercentageAttribute(): float
    {
        $cacheKey = $this->generateCacheKey('percentage');
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function () {
            $totalVotes = $this->poll->total_votes;
            
            if ($totalVotes === 0) {
                return 0;
            }
            
            return round(($this->vote_count / $totalVotes) * 100, 1);
        });
    }

    /**
     * Check if this option has the most votes
     */
    public function isWinningOption(): bool
    {
        $winningOption = $this->poll->winning_option;
        
        return $winningOption && $winningOption->id === $this->id;
    }
}
