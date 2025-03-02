<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupTopicReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'group_topic_id',
        'user_id',
        'parent_id',
        'is_solution',
    ];

    protected $casts = [
        'is_solution' => 'boolean',
    ];

    public function topic()
    {
        return $this->belongsTo(GroupTopic::class, 'group_topic_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(GroupTopicReply::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(GroupTopicReply::class, 'parent_id');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function reactions()
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }

    public function markAsSolution()
    {
        $this->update(['is_solution' => true]);
        
        // Update the topic to indicate it has a solution
        $this->topic->update(['has_solution' => true]);
    }

    public function unmarkAsSolution()
    {
        $this->update(['is_solution' => false]);
        
        // Check if there are any other solutions
        $hasSolution = $this->topic->replies()->where('is_solution', true)->exists();
        
        // Update the topic accordingly
        $this->topic->update(['has_solution' => $hasSolution]);
    }
}
