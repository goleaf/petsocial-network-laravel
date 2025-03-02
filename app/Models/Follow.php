<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    use HasFactory;

    protected $fillable = [
        'follower_id',
        'followed_id',
        'notify',
    ];

    protected $casts = [
        'notify' => 'boolean',
    ];

    /**
     * Get the user who is following.
     */
    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    /**
     * Get the user who is being followed.
     */
    public function followed()
    {
        return $this->belongsTo(User::class, 'followed_id');
    }

    /**
     * Toggle notification settings for this follow relationship.
     */
    public function toggleNotifications()
    {
        $this->update([
            'notify' => !$this->notify,
        ]);

        return $this;
    }
}
