<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'email', 'password', 'role', 'profile_visibility', 'posts_visibility', 'suspended_at', 'suspension_ends_at', 'suspension_reason'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $dates = ['banned_at', 'suspended_at', 'suspension_ends_at'];

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

    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id');
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id');
    }

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

    public function sentFriendRequests()
    {
        return $this->hasMany(FriendRequest::class, 'sender_id');
    }

    public function receivedFriendRequests()
    {
        return $this->hasMany(FriendRequest::class, 'receiver_id');
    }

    public function friends()
    {
        return $this->belongsToMany(User::class, 'friend_requests', 'sender_id', 'receiver_id')
            ->wherePivot('status', 'accepted')
            ->union(
                $this->belongsToMany(User::class, 'friend_requests', 'receiver_id', 'sender_id')
                    ->wherePivot('status', 'accepted')
            );
    }

    public function pendingSentRequests()
    {
        return $this->sentFriendRequests()->where('status', 'pending');
    }

    public function pendingReceivedRequests()
    {
        return $this->receivedFriendRequests()->where('status', 'pending');
    }

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

}
