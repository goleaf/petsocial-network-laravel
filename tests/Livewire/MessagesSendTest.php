<?php

use App\Events\MessageSent;
use App\Http\Livewire\Messages as MessagesComponent;
use App\Models\Friendship;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

it('dispatches the message sent event and refreshes the thread after sending', function (): void {
    // Prevent real events from firing so we can assert against the dispatch payload directly.
    Event::fake([MessageSent::class]);

    // Craft the two participants of the conversation.
    $author = User::factory()->create();
    $friend = User::factory()->create();

    // Mirror production behaviour by storing an accepted friendship between the users.
    Friendship::create([
        'sender_id' => $author->id,
        'recipient_id' => $friend->id,
        'status' => Friendship::STATUS_ACCEPTED,
        'accepted_at' => now(),
    ]);

    // Provide the guard with an instance whose friends relation is preloaded for the component mount cycle.
    $author->setRelation('friends', collect([$friend]));
    actingAs($author);

    // Drive the Livewire component through the send lifecycle.
    Livewire::test(MessagesComponent::class)
        ->set('receiverId', $friend->id)
        ->set('content', 'Fresh message')
        ->call('send')
        ->assertSet('content', '')
        ->assertSet('messages', function (array $messages): bool {
            // Confirm the freshly persisted message appears at the end of the thread payload.
            $latest = collect($messages)->last();

            return $latest !== null && $latest['content'] === 'Fresh message';
        });

    // Ensure the message was stored correctly.
    expect(Message::where('sender_id', $author->id)->where('receiver_id', $friend->id)->count())->toBe(1);

    // Confirm the broadcasting event was fired with the expected message instance.
    Event::assertDispatched(MessageSent::class, function (MessageSent $event) use ($author, $friend): bool {
        return $event->message->sender_id === $author->id
            && $event->message->receiver_id === $friend->id
            && $event->message->content === 'Fresh message';
    });
});
