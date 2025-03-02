<?php

namespace App\Models\Merged;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Cache;

class UserActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'description',
        'data',
        'actor_type',
        'actor_id',
        'target_type',
        'target_id',
        'read',
    ];

    protected $casts = [
        'data' => 'array',
        'read' => 'boolean',
    ];

    /**
     * Get the user that owns the activity
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the actor model (polymorphic)
     */
    public function actor(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the target model (polymorphic)
     */
    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include unread activities
     */
    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    /**
     * Scope a query to only include activities of a certain type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Mark the activity as read
     */
    public function markAsRead(): self
    {
        $this->update(['read' => true]);
        
        // Clear cache
        Cache::forget("user_{$this->user_id}_unread_activities_count");
        
        return $this;
    }

    /**
     * Create a new activity
     */
    public static function createActivity(
        string $type,
        User $user,
        ?Model $actor = null,
        ?Model $target = null,
        array $data = []
    ): self {
        $activity = new static([
            'user_id' => $user->id,
            'type' => $type,
            'data' => $data,
            'read' => false,
        ]);

        if ($actor) {
            $activity->actor_type = get_class($actor);
            $activity->actor_id = $actor->id;
        }

        if ($target) {
            $activity->target_type = get_class($target);
            $activity->target_id = $target->id;
        }

        $activity->save();
        
        // Clear cache
        Cache::forget("user_{$user->id}_recent_activities");
        Cache::forget("user_{$user->id}_unread_activities_count");

        return $activity;
    }

    /**
     * Get activity types
     */
    public static function getActivityTypes(): array
    {
        return [
            'login' => 'User Login',
            'logout' => 'User Logout',
            'profile_update' => 'Profile Update',
            'friend_request_sent' => 'Friend Request Sent',
            'friend_request_accepted' => 'Friend Request Accepted',
            'friend_request_declined' => 'Friend Request Declined',
            'post_created' => 'Post Created',
            'post_liked' => 'Post Liked',
            'post_commented' => 'Post Commented',
            'post_shared' => 'Post Shared',
            'pet_added' => 'Pet Added',
            'pet_updated' => 'Pet Updated',
            'pet_removed' => 'Pet Removed',
            'followed_user' => 'Followed User',
            'unfollowed_user' => 'Unfollowed User',
        ];
    }
}
