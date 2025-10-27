<?php

use App\Http\Livewire\Messages as MessagesComponent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Carbon;
use function Pest\Laravel\actingAs;

it('transforms stored messages into the simplified array the UI expects', function (): void {
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
        'created_at' => now()->subMinutes(2),
        'updated_at' => now()->subMinutes(2),
    ]);

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
        ->and($component->messages[0]['created_at'])
        ->toBe(now()->subMinutes(2)->toISOString());

    // Clear the mocked time to avoid leaking state into other unit tests.
    Carbon::setTestNow();
});
