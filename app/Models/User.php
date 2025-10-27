<?php

namespace App\Models;

use App\Models\Traits\HasPolymorphicRelations;
use App\Traits\ActivityTrait;
use App\Traits\EntityTypeTrait;
use App\Traits\HasFriendships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use ActivityTrait, EntityTypeTrait, HasApiTokens, HasFactory, HasFriendships, HasPolymorphicRelations, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'email', 'password', 'role', 'profile_visibility', 'posts_visibility', 'privacy_settings', 'suspended_at', 'suspension_ends_at', 'suspension_reason', 'two_factor_enabled', 'two_factor_secret', 'two_factor_recovery_codes', 'location', 'notification_preferences'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'two_factor_enabled' => 'boolean',
        'two_factor_recovery_codes' => 'array',
        'notification_preferences' => 'array',
        'privacy_settings' => 'array',
    ];

    protected $dates = ['banned_at', 'suspended_at', 'suspension_ends_at', 'deactivated_at'];

    /**
     * Default privacy settings applied to every user record.
     */
    public const PRIVACY_DEFAULTS = [
        'basic_info' => 'public',
        'stats' => 'public',
        'friends' => 'friends',
        'mutual_friends' => 'friends',
        'pets' => 'public',
        'activity' => 'friends',
    ];

    /**
     * Available visibility options for every privacy-controlled section.
     */
    public const PRIVACY_VISIBILITY_OPTIONS = ['public', 'friends', 'private'];

    /**
     * Initialize the entity type and ID for the User model
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            $user->initializeEntity('user', $user->id);
        });

        static::retrieved(function ($user) {
            $user->initializeEntity('user', $user->id);
        });
    }

    /**
     * Get the activities for this user
     */
    public function activities()
    {
        return $this->hasMany(UserActivity::class);
    }

    /**
     * Get the users that this user is following.
     */
    public function following()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followed_id')
            ->withPivot('notify')
            ->withTimestamps();
    }

    /**
     * Get the users that are following this user.
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'followed_id', 'follower_id')
            ->withPivot('notify')
            ->withTimestamps();
    }

    /**
     * Check if user is following another user.
     */
    public function isFollowing(User $user)
    {
        return $this->following()->where('followed_id', $user->id)->exists();
    }

    /**
     * Check if user is followed by another user.
     */
    public function isFollowedBy(User $user)
    {
        return $this->followers()->where('follower_id', $user->id)->exists();
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    // These methods are defined above with better implementation

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions()
    {
        return $this->hasMany(Reaction::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function shares()
    {
        return $this->hasMany(Share::class);
    }

    /**
     * Retrieve the merged privacy settings with fallback defaults.
     */
    public function mergedPrivacySettings(): array
    {
        return array_merge(self::PRIVACY_DEFAULTS, $this->privacy_settings ?? []);
    }

    /**
     * Resolve the visibility setting for a specific privacy-controlled section.
     */
    public function privacyVisibilityFor(string $section): string
    {
        $settings = $this->mergedPrivacySettings();

        return $settings[$section] ?? self::PRIVACY_DEFAULTS[$section] ?? 'public';
    }

    /**
     * Determine whether the given viewer can see the requested section.
     */
    public function canViewPrivacySection(?User $viewer, string $section): bool
    {
        if ($viewer && $viewer->id === $this->id) {
            return true;
        }

        $visibility = $this->privacyVisibilityFor($section);

        return match ($visibility) {
            'public' => true,
            'friends' => $viewer ? $viewer->isFriendWith($this->id) : false,
            'private' => false,
            default => true,
        };
    }

    // Legacy methods for FriendRequest model - replaced by Friendship model above

    public function pets()
    {
        return $this->hasMany(Pet::class);
    }

    public function isBanned()
    {
        return ! is_null($this->banned_at);
    }

    /**
     * Determine whether the user currently has an active suspension.
     *
     * The method also clears expired suspensions so that stale records do not
     * linger in the database or confuse administrators reviewing user status.
     */
    public function isSuspended(): bool
    {
        if ($this->suspended_at === null) {
            return false;
        }

        $endsAt = $this->suspension_ends_at;

        if (is_string($endsAt)) {
            $endsAt = Carbon::parse($endsAt);
        }

        if ($endsAt instanceof Carbon && $endsAt->isPast()) {
            $this->unsuspend('Suspension period elapsed automatically.', true);

            return false;
        }

        return true;
    }

    /**
     * Suspend the user and record the moderation decision.
     */
    public function suspend(?int $days = null, ?string $reason = null, bool $automated = false): void
    {
        $suspensionStartedAt = now();
        $suspensionEndsAt = $days ? $suspensionStartedAt->copy()->addDays($days) : null;

        $this->forceFill([
            'suspended_at' => $suspensionStartedAt,
            'suspension_ends_at' => $suspensionEndsAt,
            'suspension_reason' => $reason,
        ])->save();

        ActivityLog::create([
            'user_id' => $this->id,
            'action' => $automated ? 'auto_suspend' : 'manual_suspend',
            'description' => $reason ?? 'User suspended without a provided reason.',
        ]);
    }

    /**
     * Remove the suspension and optionally note how it was cleared.
     */
    public function unsuspend(?string $note = null, bool $automated = false): void
    {
        $this->forceFill([
            'suspended_at' => null,
            'suspension_ends_at' => null,
            'suspension_reason' => null,
        ])->save();

        ActivityLog::create([
            'user_id' => $this->id,
            'action' => $automated ? 'auto_unsuspend' : 'manual_unsuspend',
            'description' => $note ?? 'Suspension cleared by administrator.',
        ]);
    }

    /**
     * Evaluate recent reports and automatically suspend the user when needed.
     */
    public function evaluateAutomatedModeration(): void
    {
        $threshold = (int) Config::get('moderation.auto_suspend.report_threshold', 0);
        $windowHours = (int) Config::get('moderation.auto_suspend.window_hours', 0);

        if ($threshold <= 0 || $windowHours <= 0) {
            return;
        }

        $windowStart = now()->subHours($windowHours);

        $postReports = PostReport::whereHas('post', function ($query) {
            $query->where('user_id', $this->id);
        })->where('created_at', '>=', $windowStart)->count();

        $commentReports = 0;

        $commentModelAvailable = class_exists('App\\Models\\Merged\\Comment', false);

        if (class_exists(CommentReport::class) && $commentModelAvailable) {
            $commentReports = CommentReport::whereHas('comment', function ($query) {
                $query->where('user_id', $this->id);
            })->where('created_at', '>=', $windowStart)->count();
        }

        $directReports = Report::where('reportable_type', self::class)
            ->where('reportable_id', $this->id)
            ->where('created_at', '>=', $windowStart)
            ->count();

        $totalReports = $postReports + $commentReports + $directReports;

        if ($totalReports < $threshold || $this->isSuspended()) {
            return;
        }

        $automaticDuration = (int) Config::get('moderation.auto_suspend.suspension_days', 0);
        $reason = Config::get('moderation.auto_suspend.reason');

        $this->suspend($automaticDuration > 0 ? $automaticDuration : null, $reason, true);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function blocks()
    {
        return $this->belongsToMany(User::class, 'blocks', 'blocker_id', 'blocked_id');
    }

    /**
     * Get IDs of all accepted friends
     */
    public function getFriendIds(): array
    {
        $friendIds = collect();
        $acceptedFriendships = $this->getAcceptedFriendships();

        foreach ($acceptedFriendships as $friendship) {
            if ($friendship->sender_id === $this->id) {
                $friendIds->push($friendship->recipient_id);
            } else {
                $friendIds->push($friendship->sender_id);
            }
        }

        return $friendIds->toArray();
    }
}
