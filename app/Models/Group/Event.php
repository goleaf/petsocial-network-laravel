<?php

namespace App\Models\Group;

use App\Models\AbstractModel;
use App\Models\Attachment;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Event extends AbstractModel
{
    use HasFactory;

    protected $table = 'group_events';

    protected $fillable = [
        'title',
        'description',
        'group_id',
        'user_id',
        'start_date',
        'end_date',
        'location',
        'location_url',
        'is_online',
        'online_meeting_url',
        'cover_image',
        'max_attendees',
        'is_published',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_online' => 'boolean',
        'is_published' => 'boolean',
    ];
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        parent::booted();
        
        static::created(function ($event) {
            $event->clearCache();
            $event->group->clearCache();
        });
        
        static::updated(function ($event) {
            $event->clearCache();
            $event->group->clearCache();
        });
        
        static::deleted(function ($event) {
            $event->clearCache();
            $event->group->clearCache();
        });
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attendees()
    {
        return $this->belongsToMany(User::class, 'group_event_attendees')
            ->withPivot('status', 'reminder_set')
            ->withTimestamps();
    }
    
    /**
     * Get the attendees count with caching
     */
    public function getAttendeesCountAttribute()
    {
        $cacheKey = $this->generateCacheKey('attendees_count');
        
        return Cache::remember($cacheKey, now()->addHours(1), function () {
            return $this->attendees()->count();
        });
    }

    public function going()
    {
        return $this->attendees()->wherePivot('status', 'going');
    }
    
    /**
     * Get the going count with caching
     */
    public function getGoingCountAttribute()
    {
        $cacheKey = $this->generateCacheKey('going_count');
        
        return Cache::remember($cacheKey, now()->addHours(1), function () {
            return $this->going()->count();
        });
    }

    public function interested()
    {
        return $this->attendees()->wherePivot('status', 'interested');
    }
    
    /**
     * Get the interested count with caching
     */
    public function getInterestedCountAttribute()
    {
        $cacheKey = $this->generateCacheKey('interested_count');
        
        return Cache::remember($cacheKey, now()->addHours(1), function () {
            return $this->interested()->count();
        });
    }

    public function notGoing()
    {
        return $this->attendees()->wherePivot('status', 'not_going');
    }
    
    /**
     * Get the not going count with caching
     */
    public function getNotGoingCountAttribute()
    {
        $cacheKey = $this->generateCacheKey('not_going_count');
        
        return Cache::remember($cacheKey, now()->addHours(1), function () {
            return $this->notGoing()->count();
        });
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
    
    /**
     * Get the comments count with caching
     */
    public function getCommentsCountAttribute()
    {
        $cacheKey = $this->generateCacheKey('comments_count');
        
        return Cache::remember($cacheKey, now()->addHours(1), function () {
            return $this->comments()->count();
        });
    }

    public function generateICalendar()
    {
        // Build a stable unique identifier for calendar clients to de-duplicate imports.
        $uid = (string) Str::uuid();
        $now = now()->utc()->format('Ymd\THis\Z');
        $start = $this->start_date->clone()->utc()->format('Ymd\THis\Z');
        $end = $this->end_date
            ? $this->end_date->clone()->utc()->format('Ymd\THis\Z')
            : $start;

        // Ensure locations remain meaningful for remote meetups while keeping physical venues intact.
        $location = $this->is_online
            ? ($this->online_meeting_url ?: 'Online Event')
            : ($this->location ?: '');

        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//PetsSocialNetwork//EN\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";
        $ical .= "BEGIN:VEVENT\r\n";
        $ical .= "UID:{$uid}\r\n";
        $ical .= "DTSTAMP:{$now}\r\n";
        $ical .= "DTSTART:{$start}\r\n";
        $ical .= "DTEND:{$end}\r\n";
        $ical .= 'SUMMARY:' . $this->escapeICalText($this->title) . "\r\n";
        $ical .= 'DESCRIPTION:' . $this->escapeICalText($this->description ?? '') . "\r\n";
        $ical .= 'LOCATION:' . $this->escapeICalText($location) . "\r\n";
        $ical .= 'URL:' . $this->escapeICalText(route('group.event', ['group' => $this->group, 'event' => $this])) . "\r\n";
        $ical .= "END:VEVENT\r\n";
        $ical .= "END:VCALENDAR\r\n";

        return $ical;
    }

    protected function escapeICalText(string $value): string
    {
        // Escape characters that hold structural meaning inside ICS payloads.
        $escaped = str_replace('\\', '\\\\', $value);
        $escaped = str_replace([',', ';'], ['\\,', '\\;'], $escaped);

        // Normalise new lines so multi-line descriptions render correctly after import.
        return str_replace(["\r\n", "\n", "\r"], '\\n', $escaped);
    }

    public function publishToSocialMedia($platforms = ['twitter', 'facebook', 'telegram'])
    {
        // This would be implemented with actual social media APIs
        $results = [];
        
        foreach ($platforms as $platform) {
            // Mock implementation - would be replaced with actual API calls
            $results[$platform] = [
                'success' => true,
                'message' => "Event published to {$platform}",
                'url' => "https://{$platform}.com/event/{$this->id}"
            ];
        }
        
        return $results;
    }

    public function isAtCapacity()
    {
        if (!$this->max_attendees) {
            return false;
        }
        
        $cacheKey = $this->generateCacheKey('is_at_capacity');
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return $this->going()->count() >= $this->max_attendees;
        });
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now());
    }

    public function scopePast($query)
    {
        return $query->where('start_date', '<', now());
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeVisible($query, User $user = null)
    {
        if (!$user) {
            return $query->whereHas('group', function ($q) {
                $q->where('visibility', Group::VISIBILITY_OPEN);
            })->where('is_published', true);
        }

        return $query->whereHas('group', function ($q) use ($user) {
            $q->visible($user);
        });
    }
}
