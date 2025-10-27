<?php

use App\Http\Livewire\Common\Friend\Suggestions;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use function Pest\Laravel\actingAs;

/**
 * Feature coverage for the Livewire friend suggestions component.
 */
describe('Friend suggestions feature flow', function () {
    it('produces mutual friend suggestions for authenticated members', function () {
        // Initialize the database schema so factories can persist their records.
        prepareTestDatabase();

        // Ensure there are no cached suggestion remnants from earlier scenarios.
        Cache::flush();

        // Create a member, a confirmed friend, and a candidate that should appear as a suggestion.
        $member = User::factory()->create();
        $mutualFriend = User::factory()->create();
        $candidate = User::factory()->create();

        // Authenticate as the member so the component resolves the correct entity context.
        actingAs($member);

        // Persist accepted friendships so the candidate shares a mutual connection with the member.
        Friendship::create([
            'sender_id' => $member->id,
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

        // Mount the Livewire component via the container to mirror the production route behaviour.
        $component = app(Suggestions::class);
        $component->mount('user', $member->id);

        // Confirm the suggestion list highlights the candidate user with the expected mutual friend count.
        $suggestions = $component->suggestions;
        expect($suggestions)->toHaveCount(1);
        expect($suggestions->first()['entity']->id)->toBe($candidate->id);
        expect($suggestions->first()['mutual_friends_count'])->toBe(1);
    });

    it('defaults to the authenticated user id when one is not provided', function () {
        // Refresh the schema to support authenticating the viewer.
        prepareTestDatabase();

        // Ensure the cache is clear so the freshly mounted component hydrates clean state.
        Cache::flush();

        // Create a member and authenticate them to simulate dashboard access.
        $member = User::factory()->create();
        actingAs($member);

        // Resolve the component via the service container just like the route would.
        $component = app(Suggestions::class);

        // Mount without an explicit identifier which should fall back to the viewer's id.
        $component->mount();

        expect($component->entityId)->toBe($member->id);
    });
});
