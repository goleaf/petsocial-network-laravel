<?php

use App\Http\Livewire\Common\Friend\Hub;
use Illuminate\Support\Facades\Cache;

it('updates the active tab when requested', function (): void {
    // Instantiate the component without touching the database to verify pure state changes.
    $component = new Hub();

    // Switch the active tab and assert that the property reflects the new selection.
    $component->setActiveTab('friends');

    // Confirm the component now targets the requested tab name.
    expect($component->activeTab)->toBe('friends');
});

it('clears cached friend metadata for the resolved entity', function (): void {
    // Spy on the cache facade to capture the keys that should be invalidated.
    Cache::spy();

    // Establish a deterministic entity context for the component before clearing caches.
    $component = new Hub();
    $component->initializeEntity('user', 7);

    // Invoke the helper and confirm every expected cache entry is forgotten.
    $component->clearFriendCache();

    // Validate that both the stats cache and the entity-level caches were purged.
    Cache::shouldHaveReceived('forget')->with('user_7_friend_stats');
    Cache::shouldHaveReceived('forget')->with('user_7_friend_ids');
    Cache::shouldHaveReceived('forget')->with('user_7_friend_count');
    Cache::shouldHaveReceived('forget')->with('user_7_friend_suggestions');
});
