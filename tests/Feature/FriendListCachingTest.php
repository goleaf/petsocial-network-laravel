<?php

use App\Http\Livewire\Common\Friend\List as FriendListComponent;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\HttpException;
use function Pest\Laravel\actingAs;

/**
 * Ensures friend list rendering caches its datasets to avoid redundant queries.
 */
it('caches friend listings and categories after rendering', function (): void {
    // Prepare an owner and an accepted friend so the component has meaningful data to cache.
    $owner = User::factory()->create([
        'privacy_settings' => ['friends' => 'public'],
    ]);
    $friend = User::factory()->create([
        'name' => 'Buddy Beacon',
        'username' => 'buddy',
    ]);

    // Persist an accepted friendship so the owner has a single cached connection.
    Friendship::create([
        'sender_id' => $owner->id,
        'recipient_id' => $friend->id,
        'status' => Friendship::STATUS_ACCEPTED,
        'category' => 'Family',
    ]);

    actingAs($owner);

    Cache::flush();

    // Render the Livewire component through Pest to trigger the cache population behaviour.
    $component = app(FriendListComponent::class);
    $component->mount('user', $owner->id);
    $component->page = 1;
    $component->search = 'buddy';

    $component->render();

    $friendCacheKey = "user_{$owner->id}_friends_buddy__page1";
    $categoryCacheKey = "user_{$owner->id}_friend_categories";

    expect(Cache::has($friendCacheKey))->toBeTrue();
    expect(Cache::get($friendCacheKey)->total())->toBe(1);
    expect(Cache::get($categoryCacheKey))->toBe(['Family']);
});

/**
 * Guards the privacy enforcement that prevents unauthorised viewers from mounting the list.
 */
it('blocks viewers from accessing private friend sections', function (): void {
    // Configure a profile owner whose friend list visibility is restricted to private.
    $owner = User::factory()->create([
        'privacy_settings' => ['friends' => 'private'],
    ]);

    // Spin up a separate viewer account that lacks administrative privileges.
    $viewer = User::factory()->create();

    actingAs($viewer);

    $component = app(FriendListComponent::class);

    // Attempting to mount the component for the private profile should raise a 403 exception.
    expect(fn () => $component->mount('user', $owner->id))
        ->toThrow(HttpException::class);
});
