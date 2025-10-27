<?php

use App\Events\MessageRead;
use App\Http\Livewire\Messages;
use App\Models\Friendship;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('marks live messages as read and dispatches receipts to the sender', function () {
    // Fake the event bus so we can assert the read receipt broadcast without triggering external services.
    Event::fake([MessageRead::class]);

    // Create a pair of users linked by an accepted friendship so they appear in each others conversation list.
    $sender = User::factory()->create();
    $receiver = User::factory()->create();

    Friendship::create([
        'sender_id' => $sender->id,
        'recipient_id' => $receiver->id,
        'status' => 'accepted',
        'accepted_at' => now(),
    ]);

    // Mount the Livewire messaging component as the receiving user and open the conversation.
    $component = Livewire::actingAs($receiver)->test(Messages::class);
    $component->call('selectConversation', $sender->id);

    // Insert a fresh message that would be received while the conversation is already open.
    $message = Message::create([
        'sender_id' => $sender->id,
        'receiver_id' => $receiver->id,
        'content' => 'Hello from the other side!',
        'read' => false,
    ]);

    // Trigger the acknowledgement helper to simulate the instant read receipt coming from the UI listener.
    $component->call('markMessagesAsRead', [$message->id]);

    // Confirm the database reflects the read state so the acknowledgement persists beyond the real-time session.
    expect($message->fresh()->read)->toBeTrue();

    // Ensure the sender would be notified about the read receipt in real-time.
    Event::assertDispatched(MessageRead::class, function (MessageRead $event) use ($message, $receiver, $sender) {
        return $event->messageIds === [$message->id]
            && $event->readerId === $receiver->id
            && $event->senderId === $sender->id;
    });
});
