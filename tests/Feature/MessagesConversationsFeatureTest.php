<?php

use App\Http\Livewire\Messages as MessagesComponent;
use App\Models\Friendship;
use App\Models\Message;
use App\Models\User;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

it('loads the friends list and primes the conversation when requested', function (): void {
    // Provision the in-memory database schema used across the messaging tests.
    prepareTestDatabase();

    // Create the authenticated user who is opening the messaging panel.
    $author = User::factory()->create();

    // Generate the friend that should appear inside the conversations list.
    $friend = User::factory()->create();

    // Persist an accepted friendship between the accounts to match production social data.
    Friendship::create([
        'sender_id' => $author->id,
        'recipient_id' => $friend->id,
        'status' => Friendship::STATUS_ACCEPTED,
        'accepted_at' => now(),
    ]);

    // Preload the friends relation so the component can immediately surface the contact list.
    $author->setRelation('friends', collect([$friend]));
    actingAs($author);

    // Store a message so triggering loadMessages() provides a thread payload to the UI.
    $message = Message::create([
        'sender_id' => $friend->id,
        'receiver_id' => $author->id,
        'content' => 'Checking in',
        'read' => false,
    ]);

    // Request the component to refresh conversations, ensuring the friend is returned and messages load.
    Livewire::test(MessagesComponent::class)
        ->set('receiverId', $friend->id)
        ->call('loadConversations')
        ->assertSet('conversations', function ($conversations) use ($friend): bool {
            // Confirm the conversations property now includes the friend from the accepted list.
            return $conversations->contains('id', $friend->id);
        })
        ->assertSet('messages', function (array $messages) use ($message): bool {
            // Validate that the stored message appears inside the hydrated conversation thread payload.
            return collect($messages)->contains(fn (array $entry): bool => $entry['id'] === $message->id);
        });
});
