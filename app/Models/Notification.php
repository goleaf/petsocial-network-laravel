<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'notifiable_id',
        'notifiable_type',
        'data',
        'read_at',
        'priority',
        'group_key',
        'is_sent_email',
        'is_sent_push',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'is_sent_email' => 'boolean',
        'is_sent_push' => 'boolean',
    ];

    // Notification types
    const TYPE_MESSAGE = 'message';
    const TYPE_FRIEND_REQUEST = 'friend_request';
    const TYPE_FRIEND_ACCEPT = 'friend_accept';
    const TYPE_POST_LIKE = 'post_like';
    const TYPE_POST_COMMENT = 'post_comment';
    const TYPE_COMMENT_REPLY = 'comment_reply';
    const TYPE_GROUP_INVITE = 'group_invite';
    const TYPE_GROUP_JOIN_REQUEST = 'group_join_request';
    const TYPE_GROUP_JOIN_APPROVED = 'group_join_approved';
    const TYPE_GROUP_TOPIC = 'group_topic';
    const TYPE_GROUP_TOPIC_REPLY = 'group_topic_reply';
    const TYPE_GROUP_EVENT = 'group_event';
    const TYPE_GROUP_EVENT_REMINDER = 'group_event_reminder';
    const TYPE_GROUP_ROLE = 'group_role';
    const TYPE_MENTION = 'mention';

    // Notification priorities
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notifiable()
    {
        return $this->morphTo();
    }

    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function markAsUnread()
    {
        $this->update(['read_at' => null]);
    }

    public function isRead()
    {
        return $this->read_at !== null;
    }

    public function isUnread()
    {
        return $this->read_at === null;
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOfPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeGrouped($query)
    {
        return $query->whereNotNull('group_key');
    }

    public function scopeUngrouped($query)
    {
        return $query->whereNull('group_key');
    }

    public function getIconAttribute()
    {
        switch ($this->type) {
            case self::TYPE_MESSAGE:
                return 'message';
            case self::TYPE_FRIEND_REQUEST:
            case self::TYPE_FRIEND_ACCEPT:
                return 'user-plus';
            case self::TYPE_POST_LIKE:
                return 'heart';
            case self::TYPE_POST_COMMENT:
            case self::TYPE_COMMENT_REPLY:
                return 'message-circle';
            case self::TYPE_GROUP_INVITE:
            case self::TYPE_GROUP_JOIN_REQUEST:
            case self::TYPE_GROUP_JOIN_APPROVED:
                return 'users';
            case self::TYPE_GROUP_TOPIC:
            case self::TYPE_GROUP_TOPIC_REPLY:
                return 'file-text';
            case self::TYPE_GROUP_EVENT:
            case self::TYPE_GROUP_EVENT_REMINDER:
                return 'calendar';
            case self::TYPE_GROUP_ROLE:
                return 'shield';
            case self::TYPE_MENTION:
                return 'at-sign';
            default:
                return 'bell';
        }
    }

    public function getColorAttribute()
    {
        switch ($this->priority) {
            case self::PRIORITY_LOW:
                return 'gray';
            case self::PRIORITY_HIGH:
                return 'red';
            default:
                return 'blue';
        }
    }
}
