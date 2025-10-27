<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'commentable_type',
        'commentable_id',
        'post_id',
        'user_id',
        'content',
        'parent_id',
        'is_pinned',
        'is_approved',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_approved' => 'boolean',
    ];

    /**
     * Get the parent commentable model (post, photo, etc.)
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who wrote the comment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment if this is a reply
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get all replies to this comment
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->with('user');
    }

    /**
     * Scope a query to only include approved comments
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope a query to only include pinned comments
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Scope a query to only include root comments (not replies)
     */
    public function scopeRootComments($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Pin this comment
     */
    public function pin(): self
    {
        $this->update(['is_pinned' => true]);

        return $this;
    }

    /**
     * Unpin this comment
     */
    public function unpin(): self
    {
        $this->update(['is_pinned' => false]);

        return $this;
    }

    /**
     * Approve this comment
     */
    public function approve(): self
    {
        $this->update(['is_approved' => true]);

        return $this;
    }

    /**
     * Reject this comment
     */
    public function reject(): self
    {
        $this->update(['is_approved' => false]);

        return $this;
    }

    /**
     * Get the comment excerpt (first 100 characters)
     */
    public function getExcerptAttribute(): string
    {
        return strlen($this->content) > 100
            ? substr($this->content, 0, 100).'...'
            : $this->content;
    }

    /**
     * Check if the comment has replies
     */
    public function hasReplies(): bool
    {
        return $this->replies()->count() > 0;
    }

    /**
     * Get the total number of replies (including nested replies)
     */
    public function getTotalRepliesCountAttribute(): int
    {
        $count = $this->replies()->count();

        foreach ($this->replies as $reply) {
            $count += $reply->total_replies_count;
        }

        return $count;
    }

    /**
     * Transform the raw comment content into HTML with mention hyperlinks.
     */
    public function formattedContent(): string
    {
        // Start with the original content so we can progressively replace mentions.
        $content = $this->content ?? '';

        // Extract every @mention token using the same matcher as the Livewire component.
        preg_match_all('/@(\w+)/', $content, $matches);

        foreach ($matches[1] as $username) {
            // Look up the mentioned user by their profile name.
            $user = User::where('name', $username)->first();

            if ($user) {
                // Replace the mention with a profile link so Filament and Livewire renders are enriched.
                $content = str_replace("@$username", "<a href='/profile/{$user->id}'>@$username</a>", $content);
            }
        }

        return $content;
    }
}
