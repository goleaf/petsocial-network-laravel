<?php

use App\Http\Livewire\Common\UnifiedSearch;
use App\Models\SavedSearch;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\actingAs;

/**
 * Feature coverage ensuring saved search maintenance flows continue working.
 */
it('allows members to remove saved searches through the discovery sidebar', function () {
    // Clear any previous cache fragments so the component works with a clean slate.
    Cache::flush();

    // Authenticate a member who already has a saved search configured.
    $member = User::factory()->create([
        'profile_visibility' => 'public',
        'posts_visibility' => 'public',
        'location' => 'Austin',
    ]);
    actingAs($member);

    // Persist a saved search definition that mirrors a complex filter set.
    $savedSearch = SavedSearch::query()->create([
        'user_id' => $member->id,
        'name' => 'Austin meetups',
        'query' => 'park meetup',
        'search_type' => 'events',
        'filters' => [
            'filter' => 'friends',
            'sort_field' => 'created_at',
            'sort_direction' => 'desc',
            'location' => 'Austin',
        ],
    ]);

    // Use a lightweight harness to exercise the Livewire behaviour without rendering blade icons.
    $component = new class extends UnifiedSearch {
        /**
         * Helper to expose the deletion action for tests without invoking Livewire rendering.
         */
        public function deleteViaTest(int $savedSearchId): void
        {
            $this->deleteSavedSearch($savedSearchId);
        }
    };

    $component->mount();
    $component->deleteViaTest($savedSearch->id);

    // Confirm that the saved search was removed so the sidebar stays in sync.
    expect(SavedSearch::query()->whereKey($savedSearch->id)->exists())->toBeFalse();
});
