<?php

use App\Http\Livewire\Common\Friend\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

/**
 * HTTP-level verification for the activity log endpoint.
 */
beforeEach(function () {
    // Refresh the schema before asserting on the HTTP response payload.
    prepareTestDatabase();
    Cache::flush();
});

it('renders the Livewire markup with the expected bindings through HTTP', function () {
    $user = User::factory()->create([
        'privacy_settings' => ['activity' => 'friends'],
    ]);

    actingAs($user);

    $response = get(route('activity', [
        'entity_type' => 'user',
        'entity_id' => $user->id,
        'typeFilter' => 'post_created',
    ]));

    // Confirm the Livewire response includes the activity filter bindings and labels.
    $response->assertOk();
    // Confirm the component alias is registered in the response markup.
    $response->assertSeeLivewire('common.friend.activity-log');
    $response->assertSee('wire:model="typeFilter"', false);
    $response->assertSee(__('friends.all_activity_types'));
});
