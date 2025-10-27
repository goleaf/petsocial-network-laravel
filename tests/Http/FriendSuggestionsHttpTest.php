<?php

use App\Models\Friendship;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

/**
 * HTTP endpoint coverage ensuring friend suggestion markup is delivered.
 */
describe('Friend suggestions HTTP responses', function () {
    it('renders suggestion names within the friend dashboard response', function () {
        // Clear cached suggestions so the HTTP render reflects the latest relationships.
        Cache::flush();

        // Create the viewer, a mutual friend, and the suggested connection.
        $viewer = User::factory()->create();
        $mutualFriend = User::factory()->create();
        $candidate = User::factory()->create();

        // Authenticate and wire up the accepted friendships that yield the suggestion.
        actingAs($viewer);

        Friendship::create([
            'sender_id' => $viewer->id,
            'recipient_id' => $mutualFriend->id,
            'status' => Friendship::STATUS_ACCEPTED,
            'accepted_at' => now(),
        ]);

        Friendship::create([
            'sender_id' => $mutualFriend->id,
            'recipient_id' => $candidate->id,
            'status' => Friendship::STATUS_ACCEPTED,
            'accepted_at' => now(),
        ]);

        // Hit the friend dashboard endpoint and confirm the initial HTML contains the suggestion data.
        $response = get('/friends/dashboard');
        $response->assertOk();
        $response->assertSee($candidate->name);
        $response->assertSee(__('friends.refresh_suggestions'));
    });
});
