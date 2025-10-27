<?php

use App\Http\Livewire\Common\Follow\Button;
use Livewire\Livewire;
use Tests\Support\FollowButtonTestHelper;
use Tests\Support\FollowButtonUserStub;

/**
 * Livewire-focused assertions for the follow button component rendering.
 */
beforeEach(function (): void {
    // Provide the fake emit macro so Livewire test harnesses do not attempt to broadcast during assertions.
    FollowButtonTestHelper::fakeLivewireEvents();
});

afterEach(function (): void {
    // Tear down the Mockery alias between Livewire test runs.
    \Mockery::close();
});

it('renders the unfollow state with notification controls visible', function (): void {
    // Configure stubs to emulate a follower that already receives notifications.
    $entity = new FollowButtonUserStub(7007, true, true);
    $target = new FollowButtonUserStub(8008);

    // Mock the underlying model lookups to return the prepared stubs.
    FollowButtonTestHelper::mockUsers($entity, $target);

    // Render the component and confirm the expected strings appear in the HTML output.
    Livewire::test(Button::class, [
        'entityType' => 'user',
        'entityId' => $entity->id,
        'targetId' => $target->id,
    ])->assertSee('Unfollow')
        ->assertSee('Notifications On')
        ->assertSeeHtml('wire:click="unfollow"');
});

it('points to the dedicated blade view when follow actions are available', function (): void {
    // Craft stubs that begin in a non-following state to validate the follow CTA wiring.
    $entity = new FollowButtonUserStub(9090, false, false);
    $target = new FollowButtonUserStub(10001);

    // Ensure the component resolves our deterministic stubs instead of hitting the database.
    FollowButtonTestHelper::mockUsers($entity, $target);

    // Render the component, confirm the primary button text, and assert the expected Blade view renders.
    Livewire::test(Button::class, [
        'entityType' => 'user',
        'entityId' => $entity->id,
        'targetId' => $target->id,
    ])->assertSee('Follow')
        ->assertViewIs('livewire.common.follow.button');
});
