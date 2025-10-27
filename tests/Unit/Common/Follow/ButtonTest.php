<?php

use App\Http\Livewire\Common\Follow\Button;
use Mockery;
use Tests\Support\FollowButtonTestHelper;
use Tests\Support\FollowButtonUserStub;
use Tests\TestCase;

/**
 * Direct unit coverage for the follow button component's internal methods.
 */
uses(TestCase::class);

afterEach(function (): void {
    // Always close Mockery after each unit test to release alias mocks.
    Mockery::close();
});

it('refreshes state from the entity follow relationship', function (): void {
    // Seed the entity stub to indicate an active follow relationship and notifications.
    $entity = new FollowButtonUserStub(3003, true, true);
    $target = new FollowButtonUserStub(4004);

    // Provide the stubs to the component by replacing the User::findOrFail lookup.
    FollowButtonTestHelper::mockUsers($entity, $target);

    // Mount the component manually and confirm the internal flags mirror the stub state.
    $component = new Button();
    $component->mount('user', $entity->id, $target->id);

    expect($component->isFollowing)->toBeTrue()
        ->and($component->isReceivingNotifications)->toBeTrue();
});

it('toggles notification preferences without affecting the follow status', function (): void {
    // Begin with an entity that already follows the target and receives notifications.
    $entity = new FollowButtonUserStub(5005, true, true);
    $target = new FollowButtonUserStub(6006);

    // Ensure the component receives the prepared stubs when resolving the entity and target.
    FollowButtonTestHelper::mockUsers($entity, $target);

    $component = new Button();
    $component->mount('user', $entity->id, $target->id);

    // Exercise the toggle operation twice and assert the follow flag never flips.
    $component->toggleNotifications();
    expect($component->isFollowing)->toBeTrue()
        ->and($component->isReceivingNotifications)->toBeFalse();

    $component->toggleNotifications();
    expect($component->isFollowing)->toBeTrue()
        ->and($component->isReceivingNotifications)->toBeTrue();
});
