<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

abstract class AbstractVotableModel extends AbstractModel
{
    /**
     * Get the user who created the votable item
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the options for this votable item
     * This method should be implemented by child classes
     */
    abstract public function options(): HasMany;
    
    /**
     * Get the votes for this votable item
     * This method should be implemented by child classes
     */
    abstract public function votes(): HasMany;
    
    /**
     * Check if the votable item is active
     */
    public function isActive(): bool
    {
        return !$this->expires_at || $this->expires_at->isFuture();
    }
    
    /**
     * Check if the votable item has expired
     */
    public function hasExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
    
    /**
     * Check if a user has voted in this votable item
     */
    public function hasUserVoted(User $user): bool
    {
        $cacheKey = $this->generateCacheKey("user_{$user->id}_voted");
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($user) {
            return $this->votes()->where('user_id', $user->id)->exists();
        });
    }
    
    /**
     * Get the total number of votes
     */
    public function getTotalVotesAttribute(): int
    {
        $cacheKey = $this->generateCacheKey('total_votes');
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return $this->votes()->count();
        });
    }
    
    /**
     * Get the options with vote counts
     */
    public function getOptionsWithVotesAttribute(): array
    {
        $cacheKey = $this->generateCacheKey('options_with_votes');
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function () {
            $options = $this->options()->withCount('votes')->get();
            $totalVotes = $this->total_votes;
            
            foreach ($options as $option) {
                $option->percentage = $totalVotes > 0 
                    ? round(($option->votes_count / $totalVotes) * 100, 1) 
                    : 0;
            }
            
            return $options->toArray();
        });
    }
    
    /**
     * Get the winning option
     */
    public function getWinningOptionAttribute()
    {
        $cacheKey = $this->generateCacheKey('winning_option');
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return $this->options()->withCount('votes')
                       ->orderByDesc('votes_count')
                       ->first();
        });
    }
    
    /**
     * Clear vote-related cache when votes change
     */
    public function clearVoteCache(): void
    {
        Cache::forget($this->generateCacheKey('total_votes'));
        Cache::forget($this->generateCacheKey('options_with_votes'));
        Cache::forget($this->generateCacheKey('winning_option'));
        // We don't clear user-specific vote cache as that would be too broad
    }
}
