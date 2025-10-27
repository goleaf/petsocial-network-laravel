<?php

use App\Http\Livewire\Common\Friend\Button;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    // Reset the transient database so HTTP endpoints operate on a clean schema.
    prepareTestDatabase();
});

it('wraps the component in an http endpoint to send requests', function () {
    // Define a temporary route that proxies HTTP input into the Livewire component.
    Route::post('/testing/friend-request', function (Request $request) {
        // Mount the component using the authenticated user context provided by the test.
        $component = app(Button::class);
        $component->mount('user', auth()->id(), (int) $request->input('target_id'));

        // Execute the sendRequest action just as the Livewire front-end would trigger it.
        $component->sendRequest();

        return response()->json([
            'status' => $component->status,
        ]);
    });

    // Prepare the sender and target so the HTTP layer has concrete IDs to work with.
    $sender = User::factory()->create();
    $target = User::factory()->create();

    // Authenticate as the sender to satisfy authorization when mounting the component.
    $this->actingAs($sender);

    // Issue the HTTP call and confirm the JSON payload mirrors the Livewire component state.
    $response = $this->postJson('/testing/friend-request', [
        'target_id' => $target->id,
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'status' => 'sent_request',
        ]);

    // Validate that the database now tracks the pending relationship created by the component.
    $exists = Friendship::where('sender_id', $sender->id)
        ->where('recipient_id', $target->id)
        ->where('status', Friendship::STATUS_PENDING)
        ->exists();

    expect($exists)->toBeTrue();
});

it('accepts pending requests through an http proxy', function () {
    // Register a disposable endpoint that drives the acceptRequest workflow.
    Route::post('/testing/friend-accept', function (Request $request) {
        // Mount the component on behalf of the authenticated user who is accepting the request.
        $component = app(Button::class);
        $component->mount('user', auth()->id(), (int) $request->input('target_id'));

        // Execute the acceptRequest action so the HTTP response mirrors Livewire behaviour.
        $component->acceptRequest();

        return response()->json([
            'status' => $component->status,
        ]);
    });

    // Create the sender who issued the request and the recipient who will accept it.
    $sender = User::factory()->create();
    $recipient = User::factory()->create();

    // Persist the pending request ahead of the HTTP interaction.
    Friendship::create([
        'sender_id' => $sender->id,
        'recipient_id' => $recipient->id,
        'status' => Friendship::STATUS_PENDING,
    ]);

    // Authenticate as the recipient so mount() resolves the correct entity context.
    $this->actingAs($recipient);

    // Post to the helper route and confirm the response mirrors the Livewire component state.
    $response = $this->postJson('/testing/friend-accept', [
        'target_id' => $sender->id,
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'status' => 'friends',
        ]);

    // Reload the friendship to confirm the record transitioned to the accepted state.
    $friendship = Friendship::first();

    expect($friendship->status)->toBe(Friendship::STATUS_ACCEPTED);
});
