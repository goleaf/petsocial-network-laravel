<?php

use App\Http\Livewire\Common\Friend\Button;
use App\Models\Friendship;
use App\Models\User;
use Livewire\Livewire;

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
