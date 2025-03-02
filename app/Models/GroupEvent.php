<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GroupEvent extends Model
{
    use HasFactory;

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

    public function going()
    {
        return $this->attendees()->wherePivot('status', 'going');
    }

    public function interested()
    {
        return $this->attendees()->wherePivot('status', 'interested');
    }

    public function notGoing()
    {
        return $this->attendees()->wherePivot('status', 'not_going');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function generateICalendar()
    {
        $uid = Str::uuid();
        $now = now()->format('Ymd\THis\Z');
        $start = $this->start_date->format('Ymd\THis\Z');
        $end = $this->end_date->format('Ymd\THis\Z');
        
        $location = $this->is_online 
            ? $this->online_meeting_url 
            : $this->location;
        
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
        $ical .= "SUMMARY:" . $this->title . "\r\n";
        $ical .= "DESCRIPTION:" . str_replace("\n", "\\n", $this->description) . "\r\n";
        $ical .= "LOCATION:" . $location . "\r\n";
        $ical .= "URL:" . route('group.event', ['group' => $this->group_id, 'event' => $this->id]) . "\r\n";
        $ical .= "END:VEVENT\r\n";
        $ical .= "END:VCALENDAR\r\n";
        
        return $ical;
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
        
        return $this->going()->count() >= $this->max_attendees;
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
