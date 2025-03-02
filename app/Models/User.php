<?php

namespace App\Models;

use App\Traits\EntityTypeTrait;
use App\Traits\HasFriendships;
use App\Traits\ActivityTrait;
use App\Models\Traits\HasPolymorphicRelations;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, EntityTypeTrait, HasFriendships, ActivityTrait, HasPolymorphicRelations;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'email', 'password', 'role', 'profile_visibility', 'posts_visibility', 'suspended_at', 'suspension_ends_at', 'suspension_reason', 'two_factor_enabled', 'two_factor_secret', 'two_factor_recovery_codes', 'location', 'notification_preferences'];

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
    ];

    protected $dates = ['banned_at', 'suspended_at', 'suspension_ends_at', 'deactivated_at'];

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

    // Legacy methods for FriendRequest model - replaced by Friendship model above

    public function pets()
    {
        return $this->hasMany(Pet::class);
    }

    public function isBanned()
    {
        return !is_null($this->banned_at);
    }

    public function isSuspended()
    {
        return $this->suspended_at && (!$this->suspension_ends_at || $this->suspension_ends_at->isFuture());
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
     * 
     * @return array
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
