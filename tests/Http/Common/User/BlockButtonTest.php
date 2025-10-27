<?php

use App\Http\Livewire\Common\User\BlockButton;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function (): void {
    // Register a lightweight route that proxies HTTP requests into the Livewire component for toggling blocks.
    Route::post('/test/users/{user}/block', function (User $user) {
        /** @var BlockButton $component */
        $component = app(BlockButton::class);
        $component->mount($user->id);
        $component->toggleBlock();

        return response()->json([
            'blocked' => $component->isBlocked,
        ]);
    })->middleware('web');
});

it('toggles block state through the HTTP proxy endpoint', function (): void {
    // Create and authenticate a user who will issue the HTTP requests.
    $blocker = User::factory()->create();
    $blocked = User::factory()->create();
    actingAs($blocker);

    // Issue the initial request to block the target account and validate the JSON response.
    $firstResponse = postJson("/test/users/{$blocked->id}/block");
    $firstResponse->assertOk()->assertJson(['blocked' => true]);
    assertDatabaseHas('blocks', [
        'blocker_id' => $blocker->id,
        'blocked_id' => $blocked->id,
    ]);

    // Call the endpoint again to unblock the user and ensure the relationship has been cleared.
    $secondResponse = postJson("/test/users/{$blocked->id}/block");
    $secondResponse->assertOk()->assertJson(['blocked' => false]);
    assertDatabaseMissing('blocks', [
        'blocker_id' => $blocker->id,
        'blocked_id' => $blocked->id,
    ]);
});
