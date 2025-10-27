<?php

use App\Http\Livewire\Common\Friend\Button;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
