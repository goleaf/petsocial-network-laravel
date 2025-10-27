<?php

use App\Http\Livewire\Common\Follow\Button;
use Livewire\Livewire;
use Mockery;
use Tests\Support\FollowButtonTestHelper;
use Tests\Support\FollowButtonUserStub;

/**
 * Ensure the follow button component behaves as expected inside a feature context.
 */
afterEach(function (): void {
    // Close Mockery so the alias mock does not leak into other tests.
    Mockery::close();
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
