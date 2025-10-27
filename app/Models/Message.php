<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    // Allow mass-assignment for the relevant message attributes so they can be filled quickly.
    protected $fillable = ['sender_id', 'receiver_id', 'content', 'read'];

    // Cast the "read" attribute to boolean to simplify read receipt checks across the app.
    protected $casts = [
        'read' => 'boolean',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

}
