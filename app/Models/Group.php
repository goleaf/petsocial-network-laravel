<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'visibility',
        'creator_id',
        'cover_image',
        'icon',
        'rules',
        'location',
    ];

    protected $casts = [
        'rules' => 'array',
    ];

    // Group visibility options
    const VISIBILITY_OPEN = 'open';
    const VISIBILITY_CLOSED = 'closed';
    const VISIBILITY_SECRET = 'secret';

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'group_members')
            ->withPivot('role', 'joined_at', 'status')
            ->withTimestamps();
    }

    public function admins()
    {
        return $this->members()->wherePivot('role', 'admin');
    }

    public function moderators()
    {
        return $this->members()->wherePivot('role', 'moderator');
    }

    public function topics()
    {
        return $this->hasMany(GroupTopic::class);
    }

    public function events()
    {
        return $this->hasMany(GroupEvent::class);
    }

    public function isAdmin(User $user)
    {
        return $this->admins()->where('users.id', $user->id)->exists();
    }

    public function isModerator(User $user)
    {
        return $this->moderators()->where('users.id', $user->id)->exists();
    }

    public function isMember(User $user)
    {
        return $this->members()->where('users.id', $user->id)->wherePivot('status', 'active')->exists();
    }

    public function isPendingMember(User $user)
    {
        return $this->members()->where('users.id', $user->id)->wherePivot('status', 'pending')->exists();
    }

    public function isOpen()
    {
        return $this->visibility === self::VISIBILITY_OPEN;
    }

    public function isClosed()
    {
        return $this->visibility === self::VISIBILITY_CLOSED;
    }

    public function isSecret()
    {
        return $this->visibility === self::VISIBILITY_SECRET;
    }

    public function scopeVisible($query, User $user = null)
    {
        if (!$user) {
            return $query->where('visibility', self::VISIBILITY_OPEN);
        }

        return $query->where(function ($q) use ($user) {
            $q->where('visibility', self::VISIBILITY_OPEN)
                ->orWhere('visibility', self::VISIBILITY_CLOSED)
                ->orWhere(function ($q2) use ($user) {
                    $q2->where('visibility', self::VISIBILITY_SECRET)
                        ->whereHas('members', function ($q3) use ($user) {
                            $q3->where('users.id', $user->id);
                        });
                });
        });
    }
}
