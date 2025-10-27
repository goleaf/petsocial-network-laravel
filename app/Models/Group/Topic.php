<?php

namespace App\Models\Group;

use App\Models\AbstractModel;
use App\Models\Attachment;
use App\Models\Poll;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class Topic extends AbstractModel
{
    use HasFactory;

    protected $table = 'group_topics';

    protected $fillable = [
        'title',
        'content',
        'group_id',
        'user_id',
        'parent_id',
        'is_pinned',
        'is_locked',
        'last_activity_at',
        'views_count',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_locked' => 'boolean',
        'last_activity_at' => 'datetime',
    ];
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        parent::booted();
        
        static::created(function ($topic) {
            $topic->clearCache();
            $topic->group->clearCache();

            // Clearing the parent cache ensures nested counts stay consistent when children are added.
            if ($topic->parent) {
                $topic->parent->clearCache();
            }
        });

        static::updated(function ($topic) {
            $topic->clearCache();
            $topic->group->clearCache();

            // Keep the parent node fresh when threading metadata changes (pinning, title updates, etc.).
            if ($topic->parent) {
                $topic->parent->clearCache();
            }
        });

        static::deleted(function ($topic) {
            $topic->clearCache();
            $topic->group->clearCache();

            // Invalidate the parent cache when descendants disappear from the hierarchy.
            if ($topic->parent) {
                $topic->parent->clearCache();
            }
        });
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        // The parent relation lets us walk up the topic tree for breadcrumb-style UIs.
        return $this->belongsTo(Topic::class, 'parent_id');
    }

    public function children()
    {
        // Children define the immediate nested topics that hang off of this discussion node.
        return $this->hasMany(Topic::class, 'parent_id');
    }

    public function childrenRecursive()
    {
        // Recursive children keeps loading nested levels so deeply threaded conversations stay intact.
        return $this->children()->with(['childrenRecursive' => function ($query) {
            $query->orderBy('created_at');
        }])->orderBy('created_at');
    }

    public function replies()
    {
        return $this->hasMany(TopicReply::class, 'group_topic_id');
    }
    
    /**
     * Get the replies count with caching
     */
    public function getRepliesCountAttribute()
    {
        $cacheKey = $this->generateCacheKey('replies_count');
        
        return Cache::remember($cacheKey, now()->addHours(1), function () {
            return $this->replies()->count();
        });
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function poll()
    {
        return $this->hasOne(Poll::class, 'group_topic_id');
    }
    
    /**
     * Get the poll with caching
     */
    public function getPollAttribute()
    {
        $cacheKey = $this->generateCacheKey('poll');
        
        return Cache::remember($cacheKey, now()->addHours(1), function () {
            return $this->poll()->first();
        });
    }

    public function hasPoll()
    {
        $cacheKey = $this->generateCacheKey('has_poll');
        
        return Cache::remember($cacheKey, now()->addHours(1), function () {
            return $this->poll()->exists();
        });
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'group_topic_participants')
            ->withTimestamps();
    }
    
    /**
     * Get the participants count with caching
     */
    public function getParticipantsCountAttribute()
    {
        $cacheKey = $this->generateCacheKey('participants_count');
        
        return Cache::remember($cacheKey, now()->addHours(1), function () {
            return $this->participants()->count();
        });
    }

    public function addView(User $user = null)
    {
        $this->increment('views_count');
        $this->clearCache();

        if ($user) {
            $this->participants()->syncWithoutDetaching([$user->id]);
            $this->clearUserCache($user);
        }
    }
    
    /**
     * Clear user-specific cache
     */
    public function clearUserCache(User $user): void
    {
        Cache::forget($this->generateCacheKey("user_{$user->id}_participated"));
    }
    
    /**
     * Check if a user has participated in this topic
     */
    public function hasParticipated(User $user): bool
    {
        $cacheKey = $this->generateCacheKey("user_{$user->id}_participated");
        
        return Cache::remember($cacheKey, now()->addHours(1), function () use ($user) {
            return $this->participants()->where('users.id', $user->id)->exists();
        });
    }

    public function scopeVisible($query, User $user = null)
    {
        if (!$user) {
            return $query->whereHas('group', function ($q) {
                $q->where('visibility', Group::VISIBILITY_OPEN);
            });
        }

        return $query->whereHas('group', function ($q) use ($user) {
            $q->visible($user);
        });
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeUnpinned($query)
    {
        return $query->where('is_pinned', false);
    }

    public function scopeRoots($query)
    {
        // Root topics have no parent and act as anchors for entire thread branches.
        return $query->whereNull('parent_id');
    }

    public function scopeLocked($query)
    {
        return $query->where('is_locked', true);
    }

    public function scopeUnlocked($query)
    {
        return $query->where('is_locked', false);
    }
}
