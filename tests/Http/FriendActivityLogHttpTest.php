<?php

use App\Http\Livewire\Common\Friend\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Tests\Support\Friend\TestActivityLogComponent;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

/**
 * HTTP-level verification for the activity log endpoint.
 */
beforeEach(function () {
    // Bind the augmented component to guarantee deterministic friend lookups.
    app()->bind(ActivityLog::class, static fn () => new TestActivityLogComponent());
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
    $response->assertSee('wire:model.live="typeFilter"', false);
    $response->assertSee(__('friends.all_activity_types'));
});
