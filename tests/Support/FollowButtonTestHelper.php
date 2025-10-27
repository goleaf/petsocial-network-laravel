<?php

namespace Tests\Support;

use App\Http\Livewire\Common\Follow\Button;
use App\Models\User;
use InvalidArgumentException;
use Mockery;

/**
 * Utility helpers that configure Mockery aliases for the follow button component tests.
 */
class FollowButtonTestHelper
{
    /**
     * Mock the static User::findOrFail lookup used by the component so tests can provide stubs.
     */
    public static function mockUsers(FollowButtonUserStub $entity, FollowButtonUserStub $target): void
    {
        // Create an alias mock so static calls to the Eloquent model resolve to our stubs during the test run.
        $alias = Mockery::mock('alias:' . User::class);

        // Return the requested stub based on the identifier provided by the component.
        $alias->shouldReceive('findOrFail')->andReturnUsing(
            static function (int $id) use ($entity, $target) {
                return match ($id) {
                    $entity->id => $entity,
                    $target->id => $target,
                    default => throw new InvalidArgumentException("Unexpected user id [{$id}] requested in Follow button tests."),
                };
            }
        );
    }

    /**
     * Provide a no-op emit macro so Livewire event dispatchers do not fire during unit-style tests.
     */
    public static function fakeLivewireEvents(): void
    {
        // Register the macro only once per process to keep the event layer quiet in manual component invocations.
        if (!Button::hasMacro('emit')) {
            Button::macro('emit', function (): void {
                // Intentionally left blank because tests assert state rather than event dispatching.
            });
        }
    }
}
