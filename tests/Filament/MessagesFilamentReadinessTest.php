<?php

use App\Http\Livewire\Messages as MessagesComponent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Carbon;
use function Pest\Laravel\actingAs;

it('provides table-friendly message rows ready for Filament widgets', function (): void {
    // Fix the clock so timestamp assertions remain deterministic when building the dataset.
    Carbon::setTestNow(now());

    // Establish both participants in the conversation.
    $author = User::factory()->create();
    $friend = User::factory()->create();

    // Authenticate as the primary user before running component logic.
    actingAs($author);

    // Create a small conversation that a Filament table or infolist widget could render.
    Message::create([
        'sender_id' => $author->id,
        'receiver_id' => $friend->id,
        'content' => 'Outbound message',
        'read' => true,
        'created_at' => now()->subMinutes(3),
        'updated_at' => now()->subMinutes(3),
    ]);

    Message::create([
        'sender_id' => $friend->id,
        'receiver_id' => $author->id,
        'content' => 'Inbound reply',
        'read' => false,
        'created_at' => now()->subMinute(),
        'updated_at' => now()->subMinute(),
    ]);

    // Load the component directly to inspect the raw data it exposes to consumers such as Filament widgets.
    $component = new MessagesComponent();
    $component->receiverId = $friend->id;
    $component->loadMessages();

    // Validate the dataset is well-structured for table rows regardless of whether Filament is installed.
    collect($component->messages)->each(function (array $row) use ($author, $friend): void {
        // The component should expose a consistent schema so Filament columns can rely on predictable keys.
        expect($row)->toHaveKeys(['id', 'sender_id', 'receiver_id', 'content', 'created_at', 'read']);
        expect($row['sender_id'])->toBeIn([$author->id, $friend->id]);
        expect($row['created_at'])->toBeString();
    });

    // Guarantee at least one unread entry exists so badge components can hook into the boolean flag.
    expect(collect($component->messages)->contains(fn ($row) => $row['read'] === false))->toBeTrue();

    // Release the frozen clock for upcoming tests.
    Carbon::setTestNow();
});
