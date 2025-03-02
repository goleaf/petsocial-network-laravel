<?php

namespace App\Models\Merged;

use App\Models\Pet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Cache;

class PetActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'pet_id',
        'type',
        'description',
        'location',
        'happened_at',
        'image',
        'is_public',
        'data',
        'actor_type',
        'actor_id',
        'target_type',
        'target_id',
        'read',
    ];

    protected $casts = [
        'happened_at' => 'datetime',
        'is_public' => 'boolean',
        'data' => 'array',
        'read' => 'boolean',
    ];

    /**
     * Get the pet that owns the activity
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
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
     * Scope a query to only include public activities
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
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
        Cache::forget("pet_{$this->pet_id}_unread_activities_count");
        
        return $this;
    }

    /**
     * Create a new activity
     */
    public static function createActivity(
        string $type,
        Pet $pet,
        ?Model $actor = null,
        ?Model $target = null,
        array $data = []
    ): self {
        $activity = new static([
            'pet_id' => $pet->id,
            'type' => $type,
            'happened_at' => now(),
            'is_public' => true,
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
        Cache::forget("pet_{$pet->id}_recent_activities");
        Cache::forget("pet_{$pet->id}_unread_activities_count");

        return $activity;
    }

    /**
     * Get activity types
     */
    public static function getActivityTypes(): array
    {
        return [
            'walk' => 'Walk',
            'play' => 'Play',
            'meal' => 'Meal',
            'sleep' => 'Sleep',
            'vet_visit' => 'Vet Visit',
            'grooming' => 'Grooming',
            'training' => 'Training',
            'medication' => 'Medication',
            'friend_added' => 'Friend Added',
            'friend_request_sent' => 'Friend Request Sent',
            'friend_request_accepted' => 'Friend Request Accepted',
            'photo_added' => 'Photo Added',
            'achievement' => 'Achievement',
            'birthday' => 'Birthday',
        ];
    }
}
