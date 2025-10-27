<?php

use App\Events\MessageRead;
use App\Http\Livewire\Messages as MessagesComponent;
use App\Models\Friendship;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Broadcast;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

it('marks unread incoming messages as read when loading a conversation', function (): void {
    // Stabilise the current time so timestamps remain predictable across assertions.
    Carbon::setTestNow(now());

    // Fake broadcasting so we can assert that read receipts are emitted without touching the network layer.
    Broadcast::fake();

    // Create a pair of users to simulate the authenticated member and their friend.
    $author = User::factory()->create();
    $friend = User::factory()->create();

    // Persist an accepted friendship to reflect the production social graph expectations.
    Friendship::create([
        'sender_id' => $author->id,
        'recipient_id' => $friend->id,
        'status' => Friendship::STATUS_ACCEPTED,
        'accepted_at' => now(),
    ]);

    // Ensure the guard uses a user instance with the friends relation already hydrated.
    $author->setRelation('friends', collect([$friend]));
    actingAs($author);

    // Seed an unread message from the friend so the component has something to acknowledge.
    $incoming = Message::create([
        'sender_id' => $friend->id,
        'receiver_id' => $author->id,
        'content' => 'Unread hello',
        'read' => false,
        'created_at' => now()->subMinute(),
        'updated_at' => now()->subMinute(),
    ]);

    // Drive the Livewire component to load the conversation thread.
    Livewire::test(MessagesComponent::class)
        ->set('receiverId', $friend->id)
        ->call('loadMessages')
        ->assertSet('messages', function (array $messages) use ($incoming): bool {
            // Confirm the returned payload includes the unread message that should now be marked as read.
            $matching = collect($messages)->firstWhere('id', $incoming->id);

            return $matching !== null && $matching['read'] === true;
        });

    // Verify the database reflects the updated read flag for the incoming message.
    expect(Message::find($incoming->id)->read)->toBeTrue();

    // Confirm the broadcast layer was instructed to send the read receipt payload.
    Broadcast::assertSent(MessageRead::class, function (MessageRead $event) use ($author, $friend, $incoming): bool {
        return $event->messageIds === [$incoming->id]
            && $event->readerId === $author->id
            && $event->senderId === $friend->id;
    });

    // Clear the test clock to avoid contaminating follow-up scenarios.
    Carbon::setTestNow();
});
