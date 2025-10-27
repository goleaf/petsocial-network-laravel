<?php

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\Support\Friend\TestActivityLogComponent;

/**
 * Unit tests targeting cache coordination within the activity log component.
 */
beforeEach(function () {
    // Prime the lightweight SQLite schema for each unit test execution.
    prepareTestDatabase();
    Cache::flush();
});

it('clears every activity cache segment for the current context', function () {
    // Instantiate the component with explicit filters and pagination state.
    $component = new TestActivityLogComponent();
    $component->entityType = 'user';
    $component->entityId = 42;
    $component->typeFilter = 'post_created';
    $component->dateFilter = 'week';
    $component->page = 2;

    // Prime the cache with the keys that should be purged.
    Cache::put('user_42_activities_post_created_week_page2', 'payload');
    Cache::put('user_42_friend_activities', 'payload');
    Cache::put('user_42_activity_stats', 'payload');

    $component->clearActivityCache();

    // Ensure every targeted cache key has been invalidated.
    expect(Cache::has('user_42_activities_post_created_week_page2'))->toBeFalse();
    expect(Cache::has('user_42_friend_activities'))->toBeFalse();
    expect(Cache::has('user_42_activity_stats'))->toBeFalse();
});

it('aggregates activity statistics and stores the summary in cache', function () {
    Carbon::setTestNow(Carbon::parse('2025-06-15 12:00:00'));

    // Create a user and seed several activities across months for coverage.
    $user = User::factory()->create();
    DB::table('user_activities')->insert([
        [
            'user_id' => $user->id,
            'activity_type' => 'post_created',
            'type' => 'post_created',
            'created_at' => Carbon::now()->subMonths(1),
            'updated_at' => Carbon::now()->subMonths(1),
        ],
        [
            'user_id' => $user->id,
            'activity_type' => 'post_created',
            'type' => 'post_created',
            'created_at' => Carbon::now()->subMonths(2),
            'updated_at' => Carbon::now()->subMonths(2),
        ],
        [
            'user_id' => $user->id,
            'activity_type' => 'profile_update',
            'type' => 'profile_update',
            'created_at' => Carbon::now()->subDays(10),
            'updated_at' => Carbon::now()->subDays(10),
        ],
    ]);

    $component = new TestActivityLogComponent();
    $component->entityType = 'user';
    $component->entityId = $user->id;

    $cacheKey = "user_{$user->id}_activity_stats";
    Cache::forget($cacheKey);

    $stats = $component->getActivityStatistics();

    // Confirm totals and per-type counts are accurate.
    expect($stats['total'])->toBe(3);
    expect($stats['by_type'])
        ->toHaveKey('post_created', 2)
        ->toHaveKey('profile_update', 1);
    expect($stats['monthly'])->not->toBeEmpty();

    // The computed stats should also be cached for subsequent calls.
    expect(Cache::get($cacheKey))
        ->toMatchArray($stats);

    Carbon::setTestNow();
});
