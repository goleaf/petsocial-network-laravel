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
    // Initialise the testing database schema before creating users and messages.
    prepareTestDatabase();

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

it('selects a conversation and refreshes the thread payload', function (): void {
    // Reset the testing schema so the messaging tables are available for this interaction.
    prepareTestDatabase();

    // Establish the messaging participants.
    $author = User::factory()->create();
    $friend = User::factory()->create();

    // Record the friendship so the component mirrors the accepted-conversation requirements.
    Friendship::create([
        'sender_id' => $author->id,
        'recipient_id' => $friend->id,
        'status' => Friendship::STATUS_ACCEPTED,
        'accepted_at' => now(),
    ]);

    // Provide the existing message that should be surfaced after selecting the conversation.
    $message = Message::create([
        'sender_id' => $friend->id,
        'receiver_id' => $author->id,
        'content' => 'Existing thread message',
        'read' => true,
    ]);

    // Hydrate the friends relation so mount() mirrors the authenticated experience.
    $author->setRelation('friends', collect([$friend]));
    actingAs($author);

    // Drive the component to select the friend and refresh the thread payload.
    Livewire::test(MessagesComponent::class)
        ->call('selectConversation', $friend->id)
        ->assertSet('receiverId', $friend->id)
        ->assertSet('messages', function (array $messages) use ($message): bool {
            // Confirm the component pulled the existing message into the reactive dataset.
            return collect($messages)->contains(fn (array $entry): bool => $entry['id'] === $message->id);
        });
});
