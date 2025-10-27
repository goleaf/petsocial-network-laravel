<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class MessageRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    /**
     * The IDs of the messages that were just marked as read.
     */
    public array $messageIds;

    /**
     * The user that read the messages.
     */
    public int $readerId;

    /**
     * The original sender that should be notified about the read receipts.
     */
    public int $senderId;

    public function __construct(array $messageIds, int $readerId, int $senderId)
    {
        // Store the list of message IDs so the front-end can update the receipt state immediately.
        $this->messageIds = $messageIds;
        // Remember the reader to display contextual information if needed by the consumer.
        $this->readerId = $readerId;
        // Notify the sender so they can reflect the read status in real-time.
        $this->senderId = $senderId;
    }

    public function broadcastOn()
    {
        // Broadcast to the sender's private chat channel so only the intended recipient receives the update.
        return new Channel('chat.' . $this->senderId);
    }

    public function broadcastWith()
    {
        // Provide the payload required by the client-side listener to toggle the read receipts.
        return [
            'message_ids' => $this->messageIds,
            'reader_id' => $this->readerId,
        ];
    }
}
