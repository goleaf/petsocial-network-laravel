<?php

use App\Http\Livewire\Common\Friend\Hub;
use App\Models\Follow;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('aggregates friendship statistics for a user entity', function (): void {
    // Ensure a clean cache state before exercising the component logic.
    Cache::flush();

    // Create the primary user and related accounts to build different friendship states.
    $owner = User::factory()->create();
    $acceptedFriend = User::factory()->create();
    $pendingRecipient = User::factory()->create();
    $pendingSender = User::factory()->create();

    // Persist accepted, sent, and received friendship requests to cover each stat bucket.
    Friendship::create([
        'sender_id' => $owner->id,
        'recipient_id' => $acceptedFriend->id,
        'status' => Friendship::STATUS_ACCEPTED,
    ]);

    Friendship::create([
        'sender_id' => $owner->id,
        'recipient_id' => $pendingRecipient->id,
        'status' => Friendship::STATUS_PENDING,
    ]);

    Friendship::create([
        'sender_id' => $pendingSender->id,
        'recipient_id' => $owner->id,
        'status' => Friendship::STATUS_PENDING,
    ]);

    // Store follow relationships so follower and following counts are available.
    Follow::create([
        'follower_id' => $acceptedFriend->id,
        'followed_id' => $owner->id,
    ]);

    Follow::create([
        'follower_id' => $owner->id,
        'followed_id' => $pendingRecipient->id,
    ]);

    // Authenticate as the owner to allow the component to infer the entity automatically.
    actingAs($owner);

    // Mount the Livewire component and confirm every statistic is populated as expected.
    Livewire::test(Hub::class)
        ->assertSet('stats.total_friends', 1)
        ->assertSet('stats.pending_sent', 1)
        ->assertSet('stats.pending_received', 1)
        ->assertSet('stats.followers', 1)
        ->assertSet('stats.following', 1);
});
