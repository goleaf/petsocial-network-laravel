<?php

use App\Http\Livewire\Common\Friend\Button;
use App\Models\Friendship;
use App\Models\User;
use Livewire\Livewire;

it('accepts incoming requests and updates status to friends', function () {
    // Create a recipient user who will interact with the component.
    $recipient = User::factory()->create();

    // Create a sender who has already dispatched a pending request to the recipient.
    $sender = User::factory()->create();

    // Persist the pending relationship so the component can detect it on mount.
    Friendship::create([
        'sender_id' => $sender->id,
        'recipient_id' => $recipient->id,
        'status' => Friendship::STATUS_PENDING,
    ]);

    // Authenticate as the recipient to authorize the Livewire component actions.
    $this->actingAs($recipient);

    // Mount the component and confirm that an incoming request is recognized immediately.
    $component = Livewire::test(Button::class, [
        'entityType' => 'user',
        'entityId' => $recipient->id,
        'targetId' => $sender->id,
    ])->assertSet('status', 'received_request');

    // Accept the pending request and ensure both UI state and database status are updated.
    $component->call('acceptRequest')
        ->assertSet('status', 'friends')
        ->assertDispatched('friendRequestAccepted', $sender->id)
        ->assertDispatched('refresh');

    // Reload the relationship record to verify that it reflects an accepted friendship.
    $friendship = Friendship::first();

    expect($friendship->status)->toBe(Friendship::STATUS_ACCEPTED);
});
