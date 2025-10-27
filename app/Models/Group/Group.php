<?php

namespace App\Models\Group;

use App\Models\AbstractModel;
use App\Models\Attachment;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class Group extends AbstractModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category_id',
        'visibility',
        'creator_id',
        'cover_image',
        'icon',
        'rules',
        'location',
        'is_active',
    ];

    protected $casts = [
        'rules' => 'array',
    ];

    protected $withCount = ['members', 'topics', 'events'];

    // Group visibility options
    const VISIBILITY_OPEN = 'open';
    const VISIBILITY_CLOSED = 'closed';
    const VISIBILITY_SECRET = 'secret';

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        parent::booted();

        static::creating(function ($group): void {
            // Ensure every group is assigned a unique slug before persistence.
            if (empty($group->slug)) {
                $group->slug = static::generateUniqueSlug($group->name);
            }
        });

        static::created(function ($group): void {
            // Refresh cached aggregates when new groups are introduced.
            $group->clearCache();
            $group->category?->clearCache();
        });

        static::updated(function ($group): void {
            // Keep cache layers in sync when group metadata is updated.
            $group->clearCache();
            $group->category?->clearCache();
        });

        static::deleted(function ($group): void {
            // Flush cache once a group has been soft deleted.
            $group->clearCache();
            $group->category?->clearCache();
        });
    }

    /**
     * Generate a unique slug for the provided group name.
     */
    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (static::withTrashed()
            ->when($ignoreId, fn (Builder $query): Builder => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = sprintf('%s-%s', $baseSlug, $counter);
            $counter++;
        }

        return $slug;
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Get the creator of the group.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get the category of the group.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the members of the group.
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'group_members')
            ->withPivot('role', 'joined_at', 'status')
            ->withTimestamps();
    }
    
    /**
     * Get the active members count with caching
     */
    public function getActiveMembersCountAttribute()
    {
        $cacheKey = $this->generateCacheKey('active_members_count');
        
        return Cache::remember($cacheKey, now()->addHours(1), function () {
            return $this->members()->wherePivot('status', 'active')->count();
        });
    }

    /**
     * Get the admins of the group.
     */
    public function admins()
    {
        return $this->members()->wherePivot('role', 'admin');
    }
    
    /**
     * Get the admins with caching
     */
    public function getAdminsAttribute()
    {
        $cacheKey = $this->generateCacheKey('admins');
        
        return Cache::remember($cacheKey, now()->addHours(1), function () {
            return $this->admins()->get();
        });
    }

    /**
     * Get the moderators of the group.
     */
    public function moderators()
    {
        return $this->members()->wherePivot('role', 'moderator');
    }
    
    /**
     * Get the moderators with caching
     */
    public function getModeratorsAttribute()
    {
        $cacheKey = $this->generateCacheKey('moderators');
        
        return Cache::remember($cacheKey, now()->addHours(1), function () {
            return $this->moderators()->get();
        });
    }

    /**
     * Get the topics of the group.
     */
    public function topics()
    {
        return $this->hasMany(Topic::class);
    }

    /**
     * Get the pinned topics of the group.
     */
    public function pinnedTopics()
    {
        return $this->topics()->where('is_pinned', true);
    }
    
    /**
     * Get the pinned topics with caching
     */
    public function getPinnedTopicsAttribute()
    {
        $cacheKey = $this->generateCacheKey('pinned_topics');
        
        return Cache::remember($cacheKey, now()->addHours(1), function () {
            return $this->pinnedTopics()->get();
        });
    }

    /**
     * Get the events of the group.
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Get the upcoming events of the group.
     */
    public function upcomingEvents()
    {
        return $this->events()->where('start_date', '>=', now());
    }
    
    /**
     * Get the upcoming events with caching
     */
    public function getUpcomingEventsAttribute()
    {
        $cacheKey = $this->generateCacheKey('upcoming_events');
        
        return Cache::remember($cacheKey, now()->addHour(), function () {
            return $this->upcomingEvents()->get();
        });
    }

    /**
     * Get the reports for the group.
     */
    public function reports()
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    /**
     * Check if the user is an admin of the group.
     */
    public function isAdmin(User $user)
    {
        $cacheKey = $this->generateCacheKey("user_{$user->id}_is_admin");
        
        return Cache::remember($cacheKey, now()->addHours(1), function () use ($user) {
            return $this->admins()->where('users.id', $user->id)->exists();
        });
    }

    /**
     * Check if the user is a moderator of the group.
     */
    public function isModerator(User $user)
    {
        $cacheKey = $this->generateCacheKey("user_{$user->id}_is_moderator");
        
        return Cache::remember($cacheKey, now()->addHours(1), function () use ($user) {
            return $this->moderators()->where('users.id', $user->id)->exists();
        });
    }

    /**
     * Check if the user is a member of the group.
     */
    public function isMember(User $user)
    {
        $cacheKey = $this->generateCacheKey("user_{$user->id}_is_member");
        
        return Cache::remember($cacheKey, now()->addHours(1), function () use ($user) {
            return $this->members()->where('users.id', $user->id)->wherePivot('status', 'active')->exists();
        });
    }

    /**
     * Check if the user has a pending membership request.
     */
    public function isPendingMember(User $user)
    {
        $cacheKey = $this->generateCacheKey("user_{$user->id}_is_pending_member");
        
        return Cache::remember($cacheKey, now()->addHours(1), function () use ($user) {
            return $this->members()->where('users.id', $user->id)->wherePivot('status', 'pending')->exists();
        });
    }

    /**
     * Check if the user is banned from the group.
     */
    public function isBanned(User $user)
    {
        $cacheKey = $this->generateCacheKey("user_{$user->id}_is_banned");
        
        return Cache::remember($cacheKey, now()->addHours(1), function () use ($user) {
            return $this->members()->where('users.id', $user->id)->wherePivot('status', 'banned')->exists();
        });
    }

    /**
     * Check if the group is open.
     */
    public function isOpen()
    {
        return $this->visibility === self::VISIBILITY_OPEN;
    }

    /**
     * Check if the group is closed.
     */
    public function isClosed()
    {
        return $this->visibility === self::VISIBILITY_CLOSED;
    }

    /**
     * Check if the group is secret.
     */
    public function isSecret()
    {
        return $this->visibility === self::VISIBILITY_SECRET;
    }

    /**
     * Check if the group is visible to a user.
     */
    public function isVisibleTo(User $user = null)
    {
        if ($this->isOpen()) {
            return true;
        }

        if (!$user) {
            return false;
        }

        if ($this->isMember($user) || $user->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Scope a query to only include groups visible to a user.
     */
    public function scopeVisible($query, User $user = null)
    {
        if (!$user) {
            return $query->where('visibility', self::VISIBILITY_OPEN);
        }

        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('visibility', self::VISIBILITY_OPEN)
                ->orWhere('visibility', self::VISIBILITY_CLOSED)
                ->orWhere(function ($q2) use ($user) {
                    $q2->where('visibility', self::VISIBILITY_SECRET)
                        ->whereHas('members', function ($q3) use ($user) {
                            $q3->where('users.id', $user->id)
                                ->where('status', 'active');
                        });
                });
        });
    }

    /**
     * Get the roles for this group.
     */
    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    /**
     * Get the default role for this group.
     */
    public function defaultRole()
    {
        $cacheKey = $this->generateCacheKey('default_role');
        
        return Cache::remember($cacheKey, now()->addDay(), function () {
            return $this->roles()->where('is_default', true)->first();
        });
    }
    
    /**
     * Clear user-specific cache when membership changes
     */
    public function clearUserCache(User $user): void
    {
        Cache::forget($this->generateCacheKey("user_{$user->id}_is_admin"));
        Cache::forget($this->generateCacheKey("user_{$user->id}_is_moderator"));
        Cache::forget($this->generateCacheKey("user_{$user->id}_is_member"));
        Cache::forget($this->generateCacheKey("user_{$user->id}_is_pending_member"));
        Cache::forget($this->generateCacheKey("user_{$user->id}_is_banned"));
    }

    /**
     * Recommend groups that align with the viewer's interests by matching joined categories.
     */
    public static function discoverByInterests(User $user, int $limit = 6): Collection
    {
        $limit = max($limit, 1);

        $cacheKey = sprintf('group_discover_interests_user_%d_limit_%d', $user->id, $limit);

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user, $limit) {
            // Identify the categories the member actively participates in so discovery stays personalised.
            $categoryIds = static::query()
                ->whereNotNull('category_id')
                ->where('is_active', true)
                ->whereHas('members', function ($query) use ($user): void {
                    $query->where('user_id', $user->id)
                        ->where('status', 'active');
                })
                ->groupBy('category_id')
                ->orderByRaw('COUNT(*) DESC')
                ->limit(5)
                ->pluck('category_id')
                ->toArray();

            // When the member has not joined any groups yet, fall back to the most active categories platform-wide.
            if (empty($categoryIds)) {
                $categoryIds = static::query()
                    ->whereNotNull('category_id')
                    ->where('is_active', true)
                    ->groupBy('category_id')
                    ->orderByRaw('COUNT(*) DESC')
                    ->limit(5)
                    ->pluck('category_id')
                    ->toArray();
            }

            if (empty($categoryIds)) {
                return collect();
            }

            // Pull more candidates than required so we can sort by category affinity before trimming to the limit.
            $groups = static::query()
                ->with('category')
                ->withCount('members')
                ->whereIn('visibility', [self::VISIBILITY_OPEN, self::VISIBILITY_CLOSED])
                ->whereIn('category_id', $categoryIds)
                ->where('is_active', true)
                ->whereDoesntHave('members', function ($query) use ($user): void {
                    $query->where('user_id', $user->id);
                })
                ->orderByDesc('members_count')
                ->limit($limit * 3)
                ->get();

            return $groups
                ->sortBy(function ($group) use ($categoryIds) {
                    // Preserve category affinity ordering so the viewer sees familiar topics first.
                    $position = array_search($group->category_id, $categoryIds, true);

                    return $position === false ? PHP_INT_MAX : $position;
                })
                ->values()
                ->take($limit);
        });
    }

    /**
     * Recommend groups where the viewer's friends are already active participants.
     */
    public static function discoverByConnections(User $user, int $limit = 6): Collection
    {
        $limit = max($limit, 1);
        $friendIds = $user->getFriendIds();

        if (empty($friendIds)) {
            // Without social connections there is nothing meaningful to recommend yet.
            return collect();
        }

        $cacheKey = sprintf('group_discover_connections_user_%d_limit_%d', $user->id, $limit);

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user, $limit, $friendIds) {
            return static::query()
                ->with('category')
                ->withCount([
                    // Count how many of the viewer's friends are active members to prioritise tight communities.
                    'members as friend_members_count' => function ($query) use ($friendIds): void {
                        $query->whereIn('user_id', $friendIds)
                            ->where('status', 'active');
                    },
                    'members',
                ])
                ->where('is_active', true)
                ->whereIn('visibility', [self::VISIBILITY_OPEN, self::VISIBILITY_CLOSED])
                ->where('friend_members_count', '>', 0)
                ->whereDoesntHave('members', function ($query) use ($user): void {
                    $query->where('user_id', $user->id);
                })
                ->orderByDesc('friend_members_count')
                ->orderByDesc('members_count')
                ->limit($limit)
                ->get();
        });
    }
}
