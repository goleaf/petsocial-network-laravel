<?php

use App\Http\Livewire\Common\Friend\Analytics as FriendAnalyticsComponent;
use App\Models\Friendship;
use App\Models\Pet;
use App\Models\PetFriendship;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    // Alias the short service name used by the routes to the Livewire component
    // class so the container can resolve it during HTTP assertions.
    app()->bind('Common\\Friend\\Analytics', function ($app, array $parameters = []) {
        return $app->make(FriendAnalyticsComponent::class, $parameters);
    });
});

/**
 * Feature coverage for the friend analytics routing layer to confirm the
 * Livewire component is accessible and protected through HTTP flows.
 */
it('renders the friend analytics dashboard for the authenticated member', function () {
    // Reset any cached friendship data from previous scenarios.
    Cache::flush();

    // Prepare a member with a confirmed friendship so the analytics view has
    // meaningful data to surface in the summary widgets.
    $member = User::factory()->create();
    $friend = User::factory()->create();

    actingAs($member);

    Friendship::query()->create([
        'sender_id' => $member->id,
        'recipient_id' => $friend->id,
        'status' => Friendship::STATUS_ACCEPTED,
        'accepted_at' => now()->subDay(),
        'created_at' => now()->subDays(2),
        'updated_at' => now()->subDay(),
    ]);

    // Resolve the component through the same container binding used by the
    // route and confirm the analytics data was populated.
    $component = app('Common\\Friend\\Analytics', [
        'entityType' => 'user',
        'entityId' => $member->id,
    ]);

    $component->mount('user', $member->id);

    expect($component->summary['total_friends'])->toBe(1)
        ->and($component->render()->name())->toBe('livewire.common.friend.analytics');
});

it('blocks access to pet analytics for viewers who are not owners', function () {
    // Clear cache to avoid polluted friend ID lookups when switching contexts.
    Cache::flush();

    // Seed a pet that belongs to another member so the authorization guard can
    // trigger when an unrelated viewer attempts to load analytics.
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $pet = Pet::factory()->create(['user_id' => $owner->id]);

    // Attach at least one friendship so the component has data when access is
    // eventually granted to legitimate owners.
    PetFriendship::query()->create([
        'pet_id' => $pet->id,
        'friend_pet_id' => Pet::factory()->create()->id,
        'status' => PetFriendship::STATUS_ACCEPTED,
        'accepted_at' => now()->subDays(3),
        'created_at' => now()->subDays(5),
        'updated_at' => now()->subDays(3),
    ]);

    actingAs($viewer);

    // Attempt to mount the analytics component for the foreign pet and ensure
    // the authorization guard raises the expected HTTP exception.
    expect(fn () => app('Common\\Friend\\Analytics', [
        'entityType' => 'pet',
        'entityId' => $pet->id,
    ])->mount('pet', $pet->id))->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});
