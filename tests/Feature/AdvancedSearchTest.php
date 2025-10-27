<?php

use App\Http\Livewire\Common\UnifiedSearch;
use App\Models\Post;
use App\Models\SavedSearch;
use App\Models\SearchHistory;
use App\Models\User;
use Livewire\Livewire;

/**
 * Feature coverage for advanced unified search capabilities.
 */
it('records search history with advanced filters', function () {
    // Prepare a user and a public post to ensure the search has visible content to index.
    $member = User::factory()->create(['location' => 'Austin']);
    $publisher = User::factory()->create(['location' => 'Austin']);

    $post = new Post(['content' => 'Weekend puppy playdate']);
    $post->user_id = $publisher->id;
    $post->posts_visibility = 'public';
    $post->save();

    $this->actingAs($member);

    Livewire::test(UnifiedSearch::class)
        ->set('query', 'playdate')
        ->set('location', 'Austin')
        ->call('$refresh');

    $history = SearchHistory::where('user_id', $member->id)->first();

    expect($history)->not->toBeNull()
        ->and($history->search_type)->toBe('all')
        ->and($history->filters['location'])->toBe('Austin')
        ->and($history->results_count)->toBeGreaterThan(0);
});

it('saves and reapplies saved searches', function () {
    // Create a user that will capture a saved search definition.
    $member = User::factory()->create(['location' => 'Chicago']);

    $this->actingAs($member);

    $component = Livewire::test(UnifiedSearch::class)
        ->set('query', 'park meetup')
        ->set('type', 'events')
        ->set('location', 'Chicago')
        ->set('newSavedSearchName', 'Chicago Events')
        ->call('saveCurrentSearch');

    $saved = SavedSearch::where('user_id', $member->id)->first();

    expect($saved)->not->toBeNull()
        ->and($saved->filters['location'])->toBe('Chicago')
        ->and($saved->search_type)->toBe('events');

    $component->call('applySavedSearch', $saved->id)
        ->assertSet('query', 'park meetup')
        ->assertSet('type', 'events')
        ->assertSet('location', 'Chicago');
});
