<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupTopic extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'group_id',
        'user_id',
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

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replies()
    {
        return $this->hasMany(GroupTopicReply::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function poll()
    {
        return $this->hasOne(Poll::class);
    }

    public function hasPoll()
    {
        return $this->poll()->exists();
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'group_topic_participants')
            ->withTimestamps();
    }

    public function addView(User $user = null)
    {
        $this->increment('views_count');

        if ($user) {
            $this->participants()->syncWithoutDetaching([$user->id]);
        }
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

    public function scopeLocked($query)
    {
        return $query->where('is_locked', true);
    }

    public function scopeUnlocked($query)
    {
        return $query->where('is_locked', false);
    }
}
