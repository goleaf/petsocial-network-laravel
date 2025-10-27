<?php

use App\Http\Livewire\Common\Friend\Analytics as FriendAnalyticsComponent;
use App\Models\Friendship;
use App\Models\Pet;
use App\Models\PetFriendship;
use App\Models\User;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    // Mirror the application container binding that resolves the analytics
    // component for the route shortcuts defined in the friends namespace.
    app()->bind('Common\\Friend\\Analytics', function ($app, array $parameters = []) {
        return $app->make(FriendAnalyticsComponent::class, $parameters);
    });
});

/**
 * Low-level HTTP coverage for the friend analytics routes to ensure middleware
 * and error handling behave as expected.
 */
it('redirects guests away from the friend analytics dashboard', function () {
    // Attempt to load the member analytics dashboard without authentication and
    // confirm the guest is redirected to the login route.
    $this->get(route('friend.analytics'))->assertRedirect(route('login'));
});

it('returns the analytics Livewire component for authenticated members', function () {
    // Reset caches to guarantee the rendered response reflects the newly seeded friendships.
    Cache::flush();

    // Establish a member with a confirmed friendship so the analytics view has
    // data to render when accessed via the standard user dashboard route.
    $member = User::factory()->create();
    $friend = User::factory()->create();

    Friendship::query()->create([
        'sender_id' => $member->id,
        'recipient_id' => $friend->id,
        'status' => Friendship::STATUS_ACCEPTED,
        'accepted_at' => now()->subDay(),
        'created_at' => now()->subDays(2),
        'updated_at' => now()->subDay(),
    ]);

    actingAs($member);

    // Confirm the HTTP response embeds the expected Livewire alias responsible
    // for the analytics dashboard rendering.
    $this->get(route('friend.analytics'))
        ->assertOk()
        ->assertSeeLivewire('common.friend.analytics');
});

it('renders pet analytics for the owning member through the route binding', function () {
    // Ensure caches from prior scenarios do not leak pet friendship metadata.
    Cache::flush();

    // Seed the pet owner and related friendships that will surface in the
    // analytics dashboard when accessed via the pet context route.
    $owner = User::factory()->create();
    $pet = Pet::factory()->create(['user_id' => $owner->id]);
    $friendPet = Pet::factory()->create();

    PetFriendship::query()->create([
        'pet_id' => $pet->id,
        'friend_pet_id' => $friendPet->id,
        'status' => PetFriendship::STATUS_ACCEPTED,
        'accepted_at' => now()->subDays(3),
        'created_at' => now()->subDays(5),
        'updated_at' => now()->subDays(3),
    ]);

    actingAs($owner);

    // Access the pet analytics route and verify the Livewire component is
    // rendered for the owner, matching the Blade expectations.
    $this->get(route('pet.analytics', ['petId' => $pet->id]))
        ->assertOk()
        ->assertSeeLivewire('common.friend.analytics');
});

it('returns not found when requesting analytics for an unknown pet', function () {
    // Authenticate as a user to mirror the guard configuration and assert that
    // attempting to mount the pet analytics component with an invalid ID fails.
    $member = User::factory()->create();
    actingAs($member);

    expect(fn () => app('Common\\Friend\\Analytics', [
        'entityType' => 'pet',
        'entityId' => 999,
    ])->mount('pet', 999))->toThrow(ModelNotFoundException::class);
});
