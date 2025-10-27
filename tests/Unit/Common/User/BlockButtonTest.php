<?php

use App\Http\Livewire\Common\User\BlockButton;
use App\Models\User;
use function Pest\Laravel\actingAs;

it('evaluates the block status without triggering Livewire rendering', function (): void {
    // Seed a pair of accounts so the component can reflect the current database state.
    $blocker = User::factory()->create();
    $blocked = User::factory()->create();

    // Authenticate once to satisfy the auth()->user() lookup within the component logic.
    actingAs($blocker);

    // Instantiate the component directly so we can probe the lifecycle methods in isolation.
    $component = app(BlockButton::class);
    $component->mount($blocked->id);

    // Confirm the component records a false positive before any relationships exist.
    expect($component->isBlocked)->toBeFalse();

    // Attach a block entry and ask the component to refresh its cached state.
    $blocker->blockedUsers()->attach($blocked->id);
    $component->checkBlockStatus();

    // The in-memory property should now match the persisted pivot relationship.
    expect($component->isBlocked)->toBeTrue();
});
