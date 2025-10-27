<?php

use App\Http\Livewire\Common\Friend\Hub;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('refreshes cached statistics when friend lifecycle events fire', function (): void {
    // Guarantee no stale cache interferes with the expectations.
    Cache::flush();

    // Authenticate as a user so the component can resolve the entity automatically.
    $user = User::factory()->create();
    actingAs($user);

    // Seed cache values that should be cleared and recalculated when the handler runs.
    Cache::put("user_{$user->id}_friend_stats", ['total_friends' => 5]);
    Cache::put("user_{$user->id}_friend_ids", [99]);

    // Trigger the event handler and ensure the recalculated stats are stored correctly.
    Livewire::test(Hub::class)
        ->call('handleFriendRequestSent')
        ->assertSet('stats.total_friends', 0);

    // Confirm the stale cache entries were purged and replaced with up-to-date values.
    expect(Cache::has("user_{$user->id}_friend_ids"))->toBeFalse()
        ->and(Cache::get("user_{$user->id}_friend_stats")['total_friends'])->toBe(0);
});
