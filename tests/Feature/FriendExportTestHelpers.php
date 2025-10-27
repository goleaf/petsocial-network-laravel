<?php

use App\Models\Friendship;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

if (! function_exists('createFriendExportUsers')) {
    /**
     * Prepare a deterministic owner/friend pair for export scenarios.
     *
     * @return array{0: \App\Models\User, 1: \App\Models\User}
     */
    function createFriendExportUsers(): array
    {
        // Clearing the cache avoids leaking friend ID lookups between tests.
        Cache::flush();

        /** @var User $owner */
        $owner = User::factory()->create([
            'name' => 'Taylor Horizon',
            'email' => 'taylor@example.com',
        ]);

        // Force fill is used so we can assign attributes that are guarded on the model in production.
        $owner->forceFill([
            'username' => 'taylor-horizon',
            'phone' => '555-0100',
            'avatar' => 'owner-avatar.png',
        ])->save();

        /** @var User $friend */
        $friend = User::factory()->create([
            'name' => 'Jordan Breeze',
            'email' => 'jordan@example.com',
        ]);

        $friend->forceFill([
            'username' => 'jordan-breeze',
            'phone' => '555-0101',
            'avatar' => 'friend-avatar.png',
        ])->save();

        // Create a symmetrical accepted friendship so both sides see the relationship as active.
        Friendship::create([
            'sender_id' => $owner->id,
            'recipient_id' => $friend->id,
            'status' => Friendship::STATUS_ACCEPTED,
            'accepted_at' => now(),
        ]);

        Friendship::create([
            'sender_id' => $friend->id,
            'recipient_id' => $owner->id,
            'status' => Friendship::STATUS_ACCEPTED,
            'accepted_at' => now(),
        ]);

        return [$owner->fresh(), $friend->fresh()];
    }
}

if (! function_exists('attachExportFollower')) {
    /**
     * Attach a follower to the provided owner so follower exports can be exercised.
     */
    function attachExportFollower(User $owner): User
    {
        /** @var User $follower */
        $follower = User::factory()->create([
            'name' => 'Morgan Follower',
            'email' => 'morgan@example.com',
        ]);

        $follower->forceFill([
            'username' => 'morgan-follower',
            'phone' => '555-0102',
        ])->save();

        // Link the follower through the pivot table used by the followers relationship.
        $owner->followers()->attach($follower->id, ['notify' => true]);

        return $follower->fresh();
    }
}
