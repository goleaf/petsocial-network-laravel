<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    /**
     * The freshly stored message that will be broadcast to listeners.
     */
    public Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn(): Channel
    {
        // Leverage a private channel so only the intended receiver hears the broadcast.
        return new PrivateChannel('chat.'.$this->message->receiver_id);
    }

    public function broadcastWith()
    {
        // Share the essential payload so clients can instantly add the new message to the thread.
        return [
            'id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'receiver_id' => $this->message->receiver_id,
            'content' => $this->message->content,
            'created_at' => $this->message->created_at->toISOString(),
            'read' => $this->message->read,
        ];
    }
}
