<?php

namespace Tests\Support\Friend;

use App\Http\Livewire\Common\Friend\ActivityLog;
use App\Models\Friendship;
use App\Models\PetFriendship;

/**
 * Test double that extends the activity log component so friend resolution
 * mirrors the production friendship trait during isolated test scenarios.
 */
class TestActivityLogComponent extends ActivityLog
{
    /**
     * Allow tests to override friend identifiers when a custom dataset is required.
     */
    public array $friendIdsOverride = [];

    /**
     * Resolve friend identifiers for both user and pet entities so cached
     * activity queries continue to behave as they do in production.
     */
    public function getFriendIds(): array
    {
        if ($this->friendIdsOverride !== []) {
            return $this->friendIdsOverride;
        }

        if ($this->entityType === 'pet') {
            return PetFriendship::query()
                ->where('status', PetFriendship::STATUS_ACCEPTED)
                ->where(function ($query) {
                    $query->where('pet_id', $this->entityId)
                        ->orWhere('friend_pet_id', $this->entityId);
                })
                ->get()
                ->map(function (PetFriendship $friendship) {
                    return $friendship->pet_id === $this->entityId
                        ? $friendship->friend_pet_id
                        : $friendship->pet_id;
                })
                ->unique()
                ->values()
                ->all();
        }

        return Friendship::query()
            ->where('status', Friendship::STATUS_ACCEPTED)
            ->where(function ($query) {
                $query->where('sender_id', $this->entityId)
                    ->orWhere('recipient_id', $this->entityId);
            })
            ->get()
            ->map(function (Friendship $friendship) {
                return $friendship->sender_id === $this->entityId
                    ? $friendship->recipient_id
                    : $friendship->sender_id;
            })
            ->unique()
            ->values()
            ->all();
    }
}
