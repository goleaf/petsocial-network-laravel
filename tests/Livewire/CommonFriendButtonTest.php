<?php

use App\Http\Livewire\Common\Friend\Button;
use App\Models\Friendship;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    // Reinitialize the SQLite memory tables for each Livewire interaction scenario.
    prepareTestDatabase();
});

it('sends a friend request and dispatches UI events', function () {
    // Provision a user that will send the request and another as the target.
    $requester = User::factory()->create();
    $target = User::factory()->create();

    // Authenticate the requester to satisfy the component authorization checks.
    $this->actingAs($requester);

    // Interact with the Livewire component and trigger the send request workflow.
    Livewire::test(Button::class, [
        'entityType' => 'user',
        'entityId' => $requester->id,
        'targetId' => $target->id,
    ])->call('sendRequest')
        ->assertSet('status', 'sent_request')
        ->assertDispatched('friendRequestSent', $target->id)
        ->assertDispatched('refresh');

    // Confirm that a pending friendship row exists linking the two users.
    $friendship = Friendship::first();

    expect($friendship)->not->toBeNull()
        ->and($friendship->sender_id)->toBe($requester->id)
        ->and($friendship->recipient_id)->toBe($target->id)
        ->and($friendship->status)->toBe(Friendship::STATUS_PENDING);
});

it('declines an incoming friend request and clears pending state', function () {
    // Provision a user receiving a request and the sender who initiated it.
    $recipient = User::factory()->create();
    $sender = User::factory()->create();

    // Store the pending request so the component begins with the expected state data.
    Friendship::create([
        'sender_id' => $sender->id,
        'recipient_id' => $recipient->id,
        'status' => Friendship::STATUS_PENDING,
    ]);

    // Authenticate as the recipient so declineRequest is authorized.
    $this->actingAs($recipient);

    // Decline the pending request and ensure Livewire signals the resulting state.
    Livewire::test(Button::class, [
        'entityType' => 'user',
        'entityId' => $recipient->id,
        'targetId' => $sender->id,
    ])->call('declineRequest')
        ->assertSet('status', 'not_friends')
        ->assertDispatched('friendRequestDeclined', $sender->id)
        ->assertDispatched('refresh');

    // Verify that the friendship transitioned to the declined status rather than lingering as pending.
    $friendship = Friendship::first();

    expect($friendship->status)->toBe(Friendship::STATUS_DECLINED);
});
