<?php

use App\Http\Livewire\Common\Follow\Button;
use Illuminate\Support\Facades\Route;
use Mockery;
use Tests\Support\FollowButtonTestHelper;
use Tests\Support\FollowButtonUserStub;

use function Pest\Laravel\postJson;

/**
 * HTTP-level coverage that exercises the follow button component via a temporary route.
 */
afterEach(function (): void {
    // Ensure Mockery aliases do not persist beyond the current HTTP scenario.
    Mockery::close();
});

it('performs the follow action when invoked through an HTTP endpoint', function (): void {
    // Set up entity and target stubs representing the request payload.
    $entity = new FollowButtonUserStub(1111);
    $target = new FollowButtonUserStub(2222);

    // Mock the static lookups so the component resolves the prepared stubs.
    FollowButtonTestHelper::mockUsers($entity, $target);

    // Register a transient route that spins up the component and returns its state in JSON.
    Route::post('/testing/follow-button', static function () use ($entity, $target) {
        $component = app(Button::class);
        $component->mount('user', $entity->id, $target->id);
        $component->follow();

        return response()->json([
            'following' => $component->isFollowing,
            'notifications' => $component->isReceivingNotifications,
        ]);
    });

    // Trigger the endpoint and verify that the JSON mirrors the expected follow state changes.
    $response = postJson('/testing/follow-button');

    $response->assertOk()
        ->assertJson([
            'following' => true,
            'notifications' => true,
        ]);
});
