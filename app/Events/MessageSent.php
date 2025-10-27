<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    /**
     * The message that is being broadcast to the chat participants.
     */
    public Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Determine the private channel that should receive the chat message event.
     */
    public function broadcastOn(): PrivateChannel
    {
        // Target the authenticated recipient via their private chat channel for confidentiality.
        return new PrivateChannel('chat.' . $this->message->receiver_id);
    }

    /**
     * Shape the payload that the front-end receives alongside the broadcast.
     */
    public function broadcastWith(): array
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
