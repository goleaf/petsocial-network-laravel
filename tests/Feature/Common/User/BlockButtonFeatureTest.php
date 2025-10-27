<?php

use App\Http\Livewire\Common\User\BlockButton;
use App\Models\User;
use Illuminate\Support\Facades\View;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

it('blocks and unblocks a user while surfacing feedback', function (): void {
    // Prepare a pair of users so the authenticated account can toggle the block state.
    $blocker = User::factory()->create();
    $blocked = User::factory()->create();

    // Authenticate as the potential blocker to exercise the Livewire component like a real request.
    actingAs($blocker);

    // Mount the component and trigger the toggle to create a block entry and flash a success message.
    $component = Livewire::test(BlockButton::class, ['userId' => $blocked->id]);
    $component->call('toggleBlock');

    // Confirm the pivot record exists and the component reports the blocked status.
    assertDatabaseHas('blocks', [
        'blocker_id' => $blocker->id,
        'blocked_id' => $blocked->id,
    ]);
    $component->assertSet('isBlocked', true);
    $component->assertSessionHas('success', "You have blocked {$blocked->name}.");

    // Toggle once more to remove the block and verify that the relationship has been detached.
    $component->call('toggleBlock');
    assertDatabaseMissing('blocks', [
        'blocker_id' => $blocker->id,
        'blocked_id' => $blocked->id,
    ]);
    $component->assertSet('isBlocked', false);
    $component->assertSessionHas('success', "You have unblocked {$blocked->name}.");

    // Ensure the table stays tidy by confirming only expected entries remain.
    assertDatabaseCount('blocks', 0);
});

it('registers and renders the dedicated block button Blade view', function (): void {
    // The component references a Blade template, so confirm the view is actually registered with Laravel.
    expect(View::exists('livewire.common.user.block-button'))->toBeTrue();

    // Authenticate a pair of users to mirror the typical render cycle for the block button component.
    $blocker = User::factory()->create();
    $blocked = User::factory()->create();
    actingAs($blocker);

    // Mount the Livewire component and ensure it renders the expected Blade view without errors.
    Livewire::test(BlockButton::class, ['userId' => $blocked->id])
        ->assertViewIs('livewire.common.user.block-button');
});
