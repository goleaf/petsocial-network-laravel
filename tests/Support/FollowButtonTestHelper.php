<?php

namespace Tests\Support;

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
}
