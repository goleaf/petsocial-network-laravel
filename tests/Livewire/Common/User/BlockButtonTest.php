<?php

use App\Http\Livewire\Common\User\BlockButton;
use App\Models\User;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

it('renders the correct button copy for each block state', function (): void {
    // Create a pair of users so we can preview both the blocked and unblocked variants.
    $blocker = User::factory()->create();
    $blocked = User::factory()->create();

    // Authenticate before mounting the component to mirror the runtime guard checks.
    actingAs($blocker);

    // Verify the default render cycle shows the "Block" call-to-action when no relationship exists.
    Livewire::test(BlockButton::class, ['userId' => $blocked->id])
        ->assertSee('Block')
        ->assertDontSee('Unblock');

    // Add the pivot entry and ensure a fresh render flips the label to "Unblock" for clarity.
    $blocker->blockedUsers()->attach($blocked->id);

    Livewire::test(BlockButton::class, ['userId' => $blocked->id])
        ->assertSee('Unblock')
        ->assertDontSee('Block');
});
