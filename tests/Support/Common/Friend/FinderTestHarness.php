<?php

namespace Tests\Support\Common\Friend;

use App\Http\Livewire\Common\Friend\Finder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

/**
 * Finder test harness decorates the production component with helper stubs so
 * integration tests can exercise the core logic without depending on optional
 * browser-only hooks that are unavailable in the testing environment.
 */
class FinderTestHarness extends Finder
{
    /**
     * Capture emitted events for verification because Livewire v3 drops the old emit helper.
     *
     * @var array<int, array{string, array<int, mixed>}> $emittedEvents
     */
    public array $emittedEvents = [];

    /**
     * Render a pared down template to avoid invoking UI-only helpers during assertions.
     */
    public function render(): View
    {
        // Provide the same data contract as the production view so tests can interrogate it.
        return view('livewire.testing.finder-harness', [
            'entity' => $this->getEntity(),
            'searchResults' => $this->search ? $this->getSearchResults() : Collection::make(),
        ]);
    }

    /**
     * Determine whether the supplied identifier matches the current entity.
     */
    public function isSelf(int $entityId): bool
    {
        return $entityId === $this->entityId;
    }

    /**
     * Delegate to the friendship trait to confirm an accepted relationship.
     */
    public function isFriend(int $entityId): bool
    {
        return $this->areFriends($entityId);
    }

    /**
     * Livewire harness does not simulate pending requests, so always return false.
     */
    public function hasPendingRequest(int $entityId): bool
    {
        return false;
    }

    /**
     * Pet-specific helpers are irrelevant to the harness scenarios, so default to false.
     */
    public function isPetFriend(int $entityId): bool
    {
        return false;
    }

    /**
     * Pet request state is not required for user-focused tests, so default to false.
     */
    public function hasPendingPetRequest(int $entityId): bool
    {
        return false;
    }

    /**
     * Stub the pet friend request action because user scenarios do not invoke it.
     */
    public function sendPetFriendRequest(int $entityId): void
    {
        // Intentionally left blank for harness coverage.
    }

    /**
     * Capture events emitted by the component so tests can assert on behaviour.
     */
    public function emit($event, ...$payload): void
    {
        $this->emittedEvents[] = [$event, $payload];
    }
}
