<?php

use App\Http\Livewire\Common\Friend\Suggestions;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Unit level expectations around the suggestion loader formatting logic.
 */
it('caches formatted suggestion collections for reuse', function () {
    // Force the cache to the in-memory array store so remember() interactions stay predictable.
    config(['cache.default' => 'array']);
    Cache::flush();

    // Prepare a partial mock so we can focus on the loadSuggestions transformation behaviour.
    $component = \Mockery::mock(Suggestions::class)->makePartial();
    $component->entityType = 'user';
    $component->entityId = 99;
    $component->limit = 3;

    // Provide lightweight entity stubs so getEntity() and getFriendSuggestions() avoid database calls.
    $entity = User::factory()->make(['id' => 99, 'name' => 'Owner User']);
    $candidate = User::factory()->make(['id' => 123, 'name' => 'Candidate User']);

    $component->shouldReceive('getEntity')->once()->andReturn($entity);
    $component->shouldReceive('getFriendSuggestions')->once()->with(3)->andReturn([
        [
            'entity' => $candidate,
            'score' => 2,
            'mutual_friends_count' => 2,
            'mutual_friends' => [
                ['id' => 1, 'name' => 'Alpha'],
                ['id' => 2, 'name' => 'Beta'],
            ],
        ],
    ]);

    // Execute the loader and capture the in-memory cache payload for verification.
    $component->loadSuggestions();
    $cachedPayload = Cache::get('user_99_suggestions');

    expect($cachedPayload)->toBeInstanceOf(Collection::class);
    expect($cachedPayload->first()['entity']->id)->toBe(123);
    expect($cachedPayload->first()['mutual_friends'])->toHaveCount(2);

    // Close the mock to keep Mockery from affecting subsequent tests.
    \Mockery::close();
});
