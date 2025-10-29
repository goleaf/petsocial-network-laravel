<?php

use App\Http\Livewire\Common\Follow\Button;
use Tests\Support\FollowButtonTestHelper;
use Tests\Support\FollowButtonUserStub;

/**
 * Direct unit coverage for the follow button component's internal methods.
 */
beforeEach(function (): void {
    // Register the fake emit macro so component instances behave predictably outside Livewire harnesses.
    FollowButtonTestHelper::fakeLivewireEvents();
});

afterEach(function (): void {
    // Clear the resolver override so subsequent tests fall back to database-backed lookups.
    Button::resolveUserUsing(null);
    Button::resolveEntityUsing(null);
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

it('requires a target identifier when mounting the component', function (): void {
    // Instantiate the component directly so we can verify the guard clause thrown during mount.
    $component = new Button();

    // Expect the guard clause to surface an InvalidArgumentException when no target id is provided.
    expect(static fn () => $component->mount('user', 42))->toThrow(\InvalidArgumentException::class);
});

it('returns the follow button blade view from the render method', function (): void {
    // Seed follow relationships so render executes with the same expectations as production usage.
    $entity = new FollowButtonUserStub(8080, true, true);
    $target = new FollowButtonUserStub(9091);

    // Supply the preconfigured stubs when the component resolves model dependencies.
    FollowButtonTestHelper::mockUsers($entity, $target);

    $component = new Button();
    $component->mount('user', $entity->id, $target->id);

    // Confirm the render output references the dedicated Blade view file.
    $view = $component->render();
    expect($view->name())->toBe('livewire.common.follow.button');
});
