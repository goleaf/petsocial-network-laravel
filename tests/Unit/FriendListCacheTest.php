<?php

use App\Http\Livewire\Common\Friend\List as FriendListComponent;
use Illuminate\Support\Facades\Cache;

/**
 * Verifies cached friendship payloads are purged whenever the component requests a refresh.
 */
it('forgets all related cache keys when clearing the friend cache', function (): void {
    // Instantiate the component directly so we can exercise the cache clearing logic in isolation.
    $component = app(FriendListComponent::class);
    $component->entityType = 'user';
    $component->entityId = 42;
    $component->search = 'scout';
    $component->categoryFilter = 'family';
    $component->page = 2;

    Cache::shouldReceive('forget')->once()->with('user_42_friends_scout_family_page2');
    Cache::shouldReceive('forget')->once()->with('user_42_friend_categories');
    Cache::shouldReceive('forget')->once()->with('user_42_friend_ids');
    Cache::shouldReceive('forget')->once()->with('user_42_friend_count');
    Cache::shouldReceive('forget')->once()->with('user_42_friend_suggestions');

    $component->clearFriendCache();
});

/**
 * Confirms pet-oriented cache keys use the correct prefix when purged.
 */
it('clears the pet-prefixed caches when requested', function (): void {
    // Instantiate the component with a pet context to exercise the alternate cache naming scheme.
    $component = app(FriendListComponent::class);
    $component->entityType = 'pet';
    $component->entityId = 8;
    $component->search = 'scout';
    $component->categoryFilter = 'playmates';
    $component->page = 3;

    Cache::shouldReceive('forget')->once()->with('pet_8_friends_scout_playmates_page3');
    Cache::shouldReceive('forget')->once()->with('pet_8_friend_categories');
    Cache::shouldReceive('forget')->once()->with('pet_8_friend_ids');
    Cache::shouldReceive('forget')->once()->with('pet_8_friend_count');
    Cache::shouldReceive('forget')->once()->with('pet_8_friend_suggestions');

    $component->clearFriendCache();
});
