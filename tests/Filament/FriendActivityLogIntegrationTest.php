<?php

use App\Http\Livewire\Common\Friend\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Tests\Support\Friend\TestActivityLogComponent;

use function Pest\Laravel\actingAs;

/**
 * Filament-oriented assertions to ensure the Livewire component exposes
 * configuration that Filament tables and widgets can consume.
 */
beforeEach(function () {
    // Resolve the enhanced component inside tests so friend hydration is reliable.
    app()->bind(ActivityLog::class, static fn () => new TestActivityLogComponent());
    Cache::flush();
});

it('exposes activity type options compatible with Filament select filters', function () {
    $user = User::factory()->create();
    actingAs($user);

    $component = app(ActivityLog::class);
    $component->mount('user', $user->id);

    $view = $component->render();
    $activityTypes = $view->getData()['activityTypes'];

    // Filament expects associative options for select-based filters.
    expect($activityTypes)
        ->toHaveKey('login')
        ->toHaveKey('post_created');
});
