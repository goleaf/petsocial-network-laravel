<?php

use App\Http\Livewire\Common\Friend\Hub;
use App\Models\Follow;
use App\Models\Friendship;
use App\Models\Pet;
use App\Models\PetFriendship;
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

it('aggregates friendship statistics for a pet entity', function (): void {
    // Reset the cache so previous runs do not influence the pet-centric assertions.
    Cache::flush();

    // Create the owner and a pet profile that will be analysed by the hub component.
    $owner = User::factory()->create();
    $pet = Pet::factory()->for($owner, 'user')->create();

    // Prepare counterpart pets representing accepted and pending relationships.
    $acceptedFriend = Pet::factory()->create();
    $pendingRecipient = Pet::factory()->create();
    $pendingSender = Pet::factory()->create();

    // Store the accepted friendship with a category so the component records the grouping count.
    PetFriendship::create([
        'pet_id' => $pet->id,
        'friend_pet_id' => $acceptedFriend->id,
        'status' => PetFriendship::STATUS_ACCEPTED,
        'category' => 'Playgroup',
    ]);

    // Persist pending relationships to exercise both sent and received counters.
    PetFriendship::create([
        'pet_id' => $pet->id,
        'friend_pet_id' => $pendingRecipient->id,
        'status' => PetFriendship::STATUS_PENDING,
    ]);

    PetFriendship::create([
        'pet_id' => $pendingSender->id,
        'friend_pet_id' => $pet->id,
        'status' => PetFriendship::STATUS_PENDING,
    ]);

    // Authenticate as the owner so the Livewire component can access the protected pet entity.
    actingAs($owner);

    // Mount the Livewire component targeting the pet profile and ensure all statistics align with expectations.
    Livewire::test(Hub::class, [
        'entityType' => 'pet',
        'entityId' => $pet->id,
    ])
        ->assertSet('stats.total_friends', 1)
        ->assertSet('stats.pending_sent', 1)
        ->assertSet('stats.pending_received', 1)
        ->assertSet('stats.categories.Playgroup', 1);
});
