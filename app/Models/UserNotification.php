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
    protected $fillable = [
        'user_id',
        'sender_id',
        'sender_type',
        'type',
        'message',
        'data',
        'action_text',
        'action_url',
        'read_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

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
