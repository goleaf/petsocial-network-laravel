<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PollOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'poll_id',
        'text',
    ];

    public function poll()
    {
        return $this->belongsTo(Poll::class);
    }

    public function votes()
    {
        return $this->hasMany(PollVote::class);
    }

    public function getVotesCountAttribute()
    {
        return $this->votes()->count();
    }

    public function getPercentageAttribute()
    {
        $totalVotes = $this->poll->total_votes;
        
        if ($totalVotes === 0) {
            return 0;
        }
        
        return round(($this->votes_count / $totalVotes) * 100, 1);
    }

    public function hasVoted(User $user)
    {
        return $this->votes()->where('user_id', $user->id)->exists();
    }
}
