<?php

use App\Http\Livewire\Messages as MessagesComponent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Carbon;
use function Pest\Laravel\actingAs;

it('transforms stored messages into the simplified array the UI expects', function (): void {
    // Ensure the transient SQLite database schema mirrors production before seeding messages.
    prepareTestDatabase();

    // Freeze time so the ISO strings produced by the component stay deterministic.
    Carbon::setTestNow(now());

    // Create the authenticated user and their conversation partner.
    $author = User::factory()->create();
    $friend = User::factory()->create();

    // Log the user in before invoking the component logic.
    actingAs($author);

    // Persist an outbound message that the component should surface in the payload.
    $outbound = Message::create([
        'sender_id' => $author->id,
        'receiver_id' => $friend->id,
        'content' => 'Hello there',
        'read' => false,
    ]);

    // Force the timestamps to the desired test window now that the record exists in the database.
    $outbound->forceFill([
        'created_at' => now()->subMinutes(2),
        'updated_at' => now()->subMinutes(2),
    ])->save();

    // Instantiate the component directly so we can exercise the transformation logic without rendering.
    $component = new MessagesComponent();
    $component->receiverId = $friend->id;

    // Execute the loader and capture the resulting array structure.
    $component->loadMessages();

    // Verify the component normalises the message structure into scalar-friendly data for Alpine.js.
    expect($component->messages)
        ->toBeArray()
        ->toHaveCount(1)
        ->and($component->messages[0])
        ->toMatchArray([
            'id' => $outbound->id,
            'sender_id' => $author->id,
            'receiver_id' => $friend->id,
            'content' => 'Hello there',
            'read' => false,
        ])
        ->and(Carbon::parse($component->messages[0]['created_at'])->diffInSeconds(now()->subMinutes(2)))
        ->toBe(0);

    // Clear the mocked time to avoid leaking state into other unit tests.
    Carbon::setTestNow();
});

it('hydrates the conversations collection with the authenticated users friends', function (): void {
    // Refresh the database so the user and friendship tables exist before creating records.
    prepareTestDatabase();

    // Provision the user that will interact with the messaging panel.
    $author = User::factory()->create();

    // Create multiple friends to verify the component handles rich contact lists.
    $friends = User::factory()->count(2)->create();

    // Connect the friendships so the component can access them through the relation cache.
    $author->setRelation('friends', $friends);
    actingAs($author);

    // Instantiate the component directly to exercise the loadConversations helper without rendering overhead.
    $component = new MessagesComponent();
    $component->loadConversations();

    // Ensure the component copied the authenticated user's friends collection verbatim.
    expect($component->conversations)->toHaveCount(2)
        ->and($component->conversations->modelKeys())->toEqual($friends->modelKeys())
        ->and($component->messages)->toBe([]);
});
