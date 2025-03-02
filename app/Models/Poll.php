<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'group_topic_id',
        'user_id',
        'expires_at',
        'allow_multiple',
        'is_anonymous',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'allow_multiple' => 'boolean',
        'is_anonymous' => 'boolean',
    ];

    public function topic()
    {
        return $this->belongsTo(GroupTopic::class, 'group_topic_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function options()
    {
        return $this->hasMany(PollOption::class);
    }

    public function votes()
    {
        return $this->hasManyThrough(PollVote::class, PollOption::class);
    }

    public function hasVoted(User $user)
    {
        return $this->votes()->where('user_id', $user->id)->exists();
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isActive()
    {
        return !$this->isExpired();
    }

    public function getTotalVotesAttribute()
    {
        return $this->votes()->count();
    }

    public function getVotersCountAttribute()
    {
        return $this->votes()->distinct('user_id')->count('user_id');
    }
}
