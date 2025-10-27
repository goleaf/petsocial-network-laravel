<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    /**
     * Fields that can be mass assigned when recording a notification snapshot.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'sender_id',
        'sender_type',
        'type',
        'category',
        'priority',
        'message',
        'data',
        'channels',
        'delivered_via',
        'batch_key',
        'scheduled_for',
        'delivered_at',
        'is_digest',
        'action_text',
        'action_url',
        'read_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    /**
     * Attribute casting rules for common notification metadata.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'channels' => 'array',
        'delivered_via' => 'array',
        'read_at' => 'datetime',
        'scheduled_for' => 'datetime',
        'delivered_at' => 'datetime',
        'is_digest' => 'boolean',
    ];

    /**
     * Scope notifications by category for filtering workflows.
     */
    public function scopeForCategory($query, ?string $category)
    {
        return $category ? $query->where('category', $category) : $query;
    }

    /**
     * Scope notifications to a particular priority level.
     */
    public function scopeForPriority($query, ?string $priority)
    {
        return $priority ? $query->where('priority', $priority) : $query;
    }

    /**
     * Restrict the query to unread notifications only.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Record the channels used to deliver the notification payload.
     */
    public function markDelivered(array $channels): void
    {
        $this->forceFill([
            'delivered_via' => array_values(array_unique($channels)),
            'delivered_at' => $this->delivered_at ?? now(),
        ])->save();
    }

    /**
     * Append aggregation context when a batched notification receives another event.
     */
    public function incrementAggregate(string $message): void
    {
        $data = $this->data ?? [];
        $aggregateCount = data_get($data, 'aggregate_count', 1) + 1;
        $messages = collect(data_get($data, 'messages', []))
            ->push($message)
            ->take(5)
            ->values()
            ->all();

        $data['aggregate_count'] = $aggregateCount;
        $data['messages'] = $messages;
        $data['last_message'] = $message;

        $summary = $aggregateCount > 1
            ? __('notifications.batch_summary', ['count' => $aggregateCount, 'last' => $message])
            : $message;

        $this->forceFill([
            'message' => $summary,
            'data' => $data,
        ])->save();
    }

    /**
     * Get the user that owns the notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sender user if the sender is a user.
     */
    public function senderUser()
    {
        return $this->sender_type === User::class
            ? $this->belongsTo(User::class, 'sender_id')
            : null;
    }

    /**
     * Get the sender pet if the sender is a pet.
     */
    public function senderPet()
    {
        return $this->sender_type === Pet::class
            ? $this->belongsTo(Pet::class, 'sender_id')
            : null;
    }

    /**
     * Mark the notification as read.
     *
     * @return $this
     */
    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->forceFill(['read_at' => now()])->save();
        }

        return $this;
    }

    /**
     * Mark the notification as unread.
     *
     * @return $this
     */
    public function markAsUnread()
    {
        if (! is_null($this->read_at)) {
            $this->forceFill(['read_at' => null])->save();
        }

        return $this;
    }

    /**
     * Determine if a notification has been read.
     *
     * @return bool
     */
    public function isRead()
    {
        return $this->read_at !== null;
    }

    /**
     * Determine if a notification has not been read.
     *
     * @return bool
     */
    public function isUnread()
    {
        return $this->read_at === null;
    }
}
