<?php

use App\Http\Livewire\Common\Follow\Button;
use Livewire\Livewire;
use Tests\Support\FollowButtonTestHelper;
use Tests\Support\FollowButtonUserStub;

/**
 * Ensure the follow button component behaves as expected inside a feature context.
 */
beforeEach(function (): void {
    // Silence Livewire event emissions so manual component calls focus strictly on state assertions.
    FollowButtonTestHelper::fakeLivewireEvents();
});

afterEach(function (): void {
    // Close Mockery so the alias mock does not leak into other tests.
    \Mockery::close();
});

it('allows an entity to follow a target through the feature workflow', function (): void {
    // Prepare stub users to stand in for the authenticated entity and the follow target.
    $entity = new FollowButtonUserStub(1001);
    $target = new FollowButtonUserStub(2002);

    // Intercept User::findOrFail calls so the component receives the deterministic stubs.
    FollowButtonTestHelper::mockUsers($entity, $target);

    // Mount the component and exercise the follow action, validating the public state updates as expected.
    Livewire::test(Button::class, [
        'entityType' => 'user',
        'entityId' => $entity->id,
        'targetId' => $target->id,
    ])->assertSet('isFollowing', false)
        ->call('follow')
        ->assertSet('isFollowing', true)
        ->assertSet('isReceivingNotifications', true);
});

it('lets a follower unsubscribe cleanly via the feature interaction', function (): void {
    // Begin with stubs that reflect an active follow so we can exercise the unfollow transition.
    $entity = new FollowButtonUserStub(3001, true, true);
    $target = new FollowButtonUserStub(4002);

    // Funnel component lookups through our predictable stubs to keep the test isolated.
    FollowButtonTestHelper::mockUsers($entity, $target);

    // Invoke the unfollow action and validate both public flags flip off as expected.
    Livewire::test(Button::class, [
        'entityType' => 'user',
        'entityId' => $entity->id,
        'targetId' => $target->id,
    ])->assertSet('isFollowing', true)
        ->call('unfollow')
        ->assertSet('isFollowing', false)
        ->assertSet('isReceivingNotifications', false);
});
