<?php

use App\Http\Livewire\Common\UnifiedSearch;
use App\Models\SearchHistory;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

/**
 * Livewire-specific regression coverage for replaying historical searches.
 */
it('reapplies history entries so advanced filters stay consistent', function () {
    // Reset cache to make sure the component reads fresh data without stale fragments.
    Cache::flush();

    // Seed and authenticate a member who has previously executed a filtered query.
    $member = User::factory()->create([
        'profile_visibility' => 'public',
        'posts_visibility' => 'public',
        'location' => 'Austin',
    ]);
    actingAs($member);

    // Record a historical search with the advanced filter payload that should be restored.
    $history = SearchHistory::query()->create([
        'user_id' => $member->id,
        'query' => 'buddy playdate',
        'search_type' => 'pets',
        'results_count' => 12,
        'filters' => [
            'filter' => 'friends',
            'sort_field' => 'name',
            'sort_direction' => 'asc',
            'location' => 'Austin',
        ],
    ]);

    // Trigger the Livewire action that rehydrates the previous filter combination.
    Livewire::test(UnifiedSearch::class)
        ->call('rerunSearchFromHistory', $history->id)
        ->assertSet('query', 'buddy playdate')
        ->assertSet('type', 'pets')
        ->assertSet('filter', 'friends')
        ->assertSet('sortField', 'name')
        ->assertSet('sortDirection', 'asc')
        ->assertSet('location', 'Austin');
});
