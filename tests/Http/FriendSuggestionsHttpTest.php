<?php

use App\Http\Livewire\Common\Friend\Suggestions;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

/**
 * HTTP endpoint coverage ensuring friend suggestion markup is delivered.
 */
describe('Friend suggestions HTTP responses', function () {
    it('renders suggestion names within the friend dashboard response', function () {
        // Set up the schema so the dashboard route has the necessary tables.
        prepareTestDatabase();

        // Clear cached suggestions so the HTTP render reflects the latest relationships.
        Cache::flush();

        // Create the viewer, a mutual friend, and the suggested connection.
        $viewer = User::factory()->create();
        $mutualFriend = User::factory()->create();
        $candidate = User::factory()->create();

        // Authenticate and wire up the accepted friendships that yield the suggestion.
        actingAs($viewer);

        // Register a lightweight route that renders the Livewire view for verification without auxiliary dependencies.
        Route::middleware('web')->get('/test-friend-dashboard', function () use ($viewer) {
            $component = app(Suggestions::class);
            $component->mount('user', $viewer->id);

            return view('livewire.common.friend.suggestions', [
                'entity' => $component->getEntity(),
                'entityType' => $component->entityType,
                'suggestions' => $component->suggestions,
            ]);
        });

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

        // Hit the dedicated test endpoint and confirm the initial HTML contains the suggestion data.
        $response = get('/test-friend-dashboard');
        $response->assertOk();
        $response->assertSee($candidate->name);
        $response->assertSee(__('friends.refresh_suggestions'));
        // Ensure the Livewire trigger is present so the refresh button reuses the component action.
        $response->assertSee('wire:click="loadSuggestions"', false);
    });
});
