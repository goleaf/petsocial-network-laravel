<?php

use App\Http\Livewire\Admin\Dashboard;
use App\Models\Post;
use App\Models\User;
use App\Models\UserActivity;
use Carbon\Carbon;

it('summarizes user, post, and activity trends over the last thirty days', function () {
    // Lock the reference time so relative date calculations yield deterministic keys.
    Carbon::setTestNow(Carbon::parse('2025-04-30 12:00:00'));

    // Seed users with specific creation dates to populate the aggregation buckets.
    $recentUser = User::withoutEvents(fn () => User::factory()->create([
        'created_at' => now()->subDays(2),
        'updated_at' => now()->subDays(2),
    ]));
    $midWindowUser = User::withoutEvents(fn () => User::factory()->create([
        'created_at' => now()->subDays(10),
        'updated_at' => now()->subDays(10),
    ]));
    User::withoutEvents(fn () => User::factory()->create([
        'created_at' => now()->subDays(40),
        'updated_at' => now()->subDays(40),
    ]));

    // Create posts across the window so the post timeline has multiple data points.
    Post::unguarded(fn () => Post::create([
        'user_id' => $recentUser->id,
        'content' => 'Fresh adventures from the park.',
        'created_at' => now()->subDays(1),
        'updated_at' => now()->subDays(1),
    ]));
    Post::unguarded(fn () => Post::create([
        'user_id' => $midWindowUser->id,
        'content' => 'Throwback fun with the pups.',
        'created_at' => now()->subDays(12),
        'updated_at' => now()->subDays(12),
    ]));
    Post::unguarded(fn () => Post::create([
        'user_id' => $midWindowUser->id,
        'content' => 'Older story outside the reporting range.',
        'created_at' => now()->subDays(45),
        'updated_at' => now()->subDays(45),
    ]));

    // Record daily activities to ensure the component recognises active members correctly.
    UserActivity::unguarded(fn () => UserActivity::create([
        'user_id' => $recentUser->id,
        'type' => 'post_created',
        'description' => 'Shared a brand new memory.',
        'created_at' => now()->subDays(3),
        'updated_at' => now()->subDays(3),
    ]));
    UserActivity::unguarded(fn () => UserActivity::create([
        'user_id' => $midWindowUser->id,
        'type' => 'commented',
        'description' => 'Replied to a friend.',
        'created_at' => now()->subDays(28),
        'updated_at' => now()->subDays(28),
    ]));
    UserActivity::unguarded(fn () => UserActivity::create([
        'user_id' => $midWindowUser->id,
        'type' => 'reacted',
        'description' => 'Outside the reporting window.',
        'created_at' => now()->subDays(50),
        'updated_at' => now()->subDays(50),
    ]));

    // Invoke the aggregation helper directly to examine the computed series.
    $stats = (new Dashboard())->getActivityStats();

    // Confirm the user creation histogram includes only rows from the trailing month.
    expect($stats['users'])
        ->toHaveKey(now()->subDays(2)->toDateString())
        ->toHaveKey(now()->subDays(10)->toDateString())
        ->not->toHaveKey(now()->subDays(40)->toDateString());

    // Validate post counts line up with the seeded entries inside the window.
    expect($stats['posts'][now()->subDays(1)->toDateString()])->toBe(1);
    expect($stats['posts'][now()->subDays(12)->toDateString()])->toBe(1);
    expect($stats['posts'])->not->toHaveKey(now()->subDays(45)->toDateString());

    // Ensure only the recent activity rows are represented in the final payload.
    expect($stats['activities'])
        ->toHaveKey(now()->subDays(3)->toDateString())
        ->toHaveKey(now()->subDays(28)->toDateString())
        ->not->toHaveKey(now()->subDays(50)->toDateString());

    // Release the mocked clock for any subsequent unit tests.
    Carbon::setTestNow();
});
