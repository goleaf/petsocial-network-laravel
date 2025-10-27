<?php

use App\Http\Livewire\Common\Follow\Button;
use Livewire\Livewire;
use Mockery;
use Tests\Support\FollowButtonTestHelper;
use Tests\Support\FollowButtonUserStub;
use Tests\TestCase;

/**
 * Livewire-focused assertions for the follow button component rendering.
 */
uses(TestCase::class);

afterEach(function (): void {
    // Tear down the Mockery alias between Livewire test runs.
    Mockery::close();
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
