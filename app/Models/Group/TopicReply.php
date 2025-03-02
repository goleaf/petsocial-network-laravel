<?php

namespace App\Models\Group;

use App\Models\AbstractModel;
use App\Models\Attachment;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class TopicReply extends AbstractModel
{
    use HasFactory;

    protected $table = 'group_topic_replies';

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
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        parent::booted();
        
        static::created(function ($reply) {
            $reply->clearCache();
            $reply->topic->clearCache();
        });
        
        static::updated(function ($reply) {
            $reply->clearCache();
            $reply->topic->clearCache();
        });
        
        static::deleted(function ($reply) {
            $reply->clearCache();
            $reply->topic->clearCache();
        });
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class, 'group_topic_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(TopicReply::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(TopicReply::class, 'parent_id');
    }
    
    /**
     * Get the children count with caching
     */
    public function getChildrenCountAttribute()
    {
        $cacheKey = $this->generateCacheKey('children_count');
        
        return Cache::remember($cacheKey, now()->addHours(1), function () {
            return $this->children()->count();
        });
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function reactions()
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }
    
    /**
     * Get the reactions count with caching
     */
    public function getReactionsCountAttribute()
    {
        $cacheKey = $this->generateCacheKey('reactions_count');
        
        return Cache::remember($cacheKey, now()->addHours(1), function () {
            return $this->reactions()->count();
        });
    }
    
    /**
     * Get the reactions summary with caching
     */
    public function getReactionsSummaryAttribute()
    {
        $cacheKey = $this->generateCacheKey('reactions_summary');
        
        return Cache::remember($cacheKey, now()->addHours(1), function () {
            return $this->reactions()
                ->select('type', \DB::raw('count(*) as count'))
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray();
        });
    }

    public function markAsSolution()
    {
        $this->update(['is_solution' => true]);
        
        // Update the topic to indicate it has a solution
        $this->topic->update(['has_solution' => true]);
        
        // Clear cache
        $this->clearCache();
        $this->topic->clearCache();
    }

    public function unmarkAsSolution()
    {
        $this->update(['is_solution' => false]);
        
        // Check if there are any other solutions
        $hasSolution = $this->topic->replies()->where('is_solution', true)->exists();
        
        // Update the topic accordingly
        $this->topic->update(['has_solution' => $hasSolution]);
        
        // Clear cache
        $this->clearCache();
        $this->topic->clearCache();
    }
}
