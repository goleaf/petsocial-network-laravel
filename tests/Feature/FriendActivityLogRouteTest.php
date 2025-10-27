<?php

use App\Http\Livewire\Common\Friend\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Tests\Support\Friend\TestActivityLogComponent;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

/**
 * Feature coverage for the dedicated activity log route.
 */
beforeEach(function () {
    // Swap the activity log component with the test double so friend lookups
    // behave consistently without modifying production code.
    app()->bind(ActivityLog::class, static fn () => new TestActivityLogComponent());
    Cache::flush();
});

it('allows authenticated owners to view their activity log feed', function () {
    // Create a user with default privacy rules that permit self access.
    $user = User::factory()->create([
        'privacy_settings' => ['activity' => 'friends'],
    ]);

    actingAs($user);

    // Hit the dedicated activity log endpoint and expect a successful render.
    $response = get(route('activity', [
        'entity_type' => 'user',
        'entity_id' => $user->id,
    ]));

    $response->assertOk();
    $response->assertSee(__('friends.all_activity_types'));
});

it('blocks unauthorized viewers from accessing private activity logs', function () {
    // Prepare two members where the target has locked down activity privacy.
    $owner = User::factory()->create([
        'privacy_settings' => ['activity' => 'private'],
    ]);
    $viewer = User::factory()->create();

    actingAs($viewer);

    // Attempting to view the private log should return a forbidden response.
    get(route('activity', [
        'entity_type' => 'user',
        'entity_id' => $owner->id,
    ]))->assertForbidden();
});
