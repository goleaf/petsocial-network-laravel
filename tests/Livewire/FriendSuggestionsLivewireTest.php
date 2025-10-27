<?php

use App\Http\Livewire\Common\Friend\Suggestions;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

/**
 * Livewire interaction tests for the friend suggestions component.
 */
describe('Friend suggestions Livewire component', function () {
    it('refreshes suggestions and exposes the hydrated entity to the view', function () {
        // Prepare the schema before creating any relational data.
        prepareTestDatabase();

        // Reset the cache to avoid stale friend suggestion payloads.
        Cache::flush();

        // Create the viewer, a mutual friend, and the candidate expected in the suggestion feed.
        $viewer = User::factory()->create();
        $mutualFriend = User::factory()->create();
        $candidate = User::factory()->create();

        // Authenticate the viewer so Livewire resolves policies and relationships correctly.
        actingAs($viewer);

        // Build accepted friendships to create a mutual connection chain.
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

        // Exercise the Livewire component just as the browser would during hydration and refresh.
        Livewire::test(Suggestions::class, [
            'entityType' => 'user',
            'entityId' => $viewer->id,
            'limit' => 5,
        ])
            // Confirm the component renders the expected Blade view for the UI surface.
            ->assertViewIs('livewire.common.friend.suggestions')
            ->call('loadSuggestions')
            ->assertViewHas('entity', fn ($entity) => $entity->is($viewer))
            ->assertSet('suggestions', function ($value) use ($candidate) {
                // Validate the property is a collection and that it lists the expected candidate.
                return $value instanceof Collection && $value->first()['entity']->id === $candidate->id;
            });
    });
});
