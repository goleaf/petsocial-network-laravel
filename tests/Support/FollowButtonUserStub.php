<?php

namespace Tests\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * Lightweight user surrogate used to mimic follow relationships inside Follow button tests.
 */
class FollowButtonUserStub extends Model
{
    /**
     * Flag indicating whether the entity currently follows the target.
     */
    public bool $following;

    /**
     * Flag tracking whether notifications are enabled for the relationship.
     */
    public bool $receivingNotifications;

    /**
     * Create a new stub user with optional follow and notification state.
     */
    public function __construct(public int $id = 0, bool $following = false, bool $receivingNotifications = false)
    {
        // Call the parent constructor to ensure the Eloquent model boots correctly for tests.
        parent::__construct();

        // Provide a predictable display name for assertions relying on rendered markup.
        $this->name = "Stub User {$id}";

        // Persist the initial relationship state flags.
        $this->following = $following;
        $this->receivingNotifications = $receivingNotifications;

        // Mark the model as existing so Livewire treats it as an already-saved record.
        $this->exists = true;
    }

    /**
     * Determine if the stub is following the provided user.
     */
    public function isFollowing(Model $user): bool
    {
        return $this->following;
    }

    /**
     * Simulate the follow action and enable notifications for the relationship.
     */
    public function follow(Model $user): void
    {
        $this->following = true;
        $this->receivingNotifications = true;
    }

    /**
     * Simulate the unfollow action and disable notifications at the same time.
     */
    public function unfollow(Model $user): void
    {
        $this->following = false;
        $this->receivingNotifications = false;
    }

    /**
     * Determine if notifications are currently enabled for the provided user.
     */
    public function isReceivingNotificationsFrom(Model $user): bool
    {
        return $this->receivingNotifications;
    }

    /**
     * Disable notifications for the follow relationship.
     */
    public function muteNotificationsFrom(Model $user): void
    {
        $this->receivingNotifications = false;
    }

    /**
     * Re-enable notifications for the follow relationship.
     */
    public function unmuteNotificationsFrom(Model $user): void
    {
        $this->receivingNotifications = true;
    }
}
