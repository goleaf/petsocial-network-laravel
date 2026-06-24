<?php

use App\Http\Livewire\UserDashboard;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

use function Pest\Laravel\actingAs;

/**
 * Feature coverage for the UserDashboard feed aggregation query.
 */
it('collects feed posts from friends, follows, and shares while filtering blocked users', function () {
    // Freeze time so the pagination order stays deterministic across assertions.
    Carbon::setTestNow(Carbon::parse('2025-05-01 12:00:00'));

    // Create the authenticated viewer and the surrounding network accounts.
    $viewer = User::factory()->create();
    $friend = User::factory()->create();
    $followed = User::factory()->create();
    $blocked = User::factory()->create();
    $publicAuthor = User::factory()->create();

    // Persist the viewer as friends with the designated account.
    DB::table('friendships')->insert([
        'sender_id' => $viewer->id,
        'recipient_id' => $friend->id,
        'status' => 'accepted',
        'created_at' => now()->subMinutes(10),
        'updated_at' => now()->subMinutes(10),
    ]);

    // Persist a follow relationship so the dashboard query includes followed users.
    DB::table('follows')->insert([
        'follower_id' => $viewer->id,
        'followed_id' => $followed->id,
        'notify' => true,
        'created_at' => now()->subMinutes(9),
        'updated_at' => now()->subMinutes(9),
    ]);

    // Persist a block record so the blocked account is filtered out unless shared.
    DB::table('blocks')->insert([
        'blocker_id' => $viewer->id,
        'blocked_id' => $blocked->id,
        'created_at' => now()->subMinutes(8),
        'updated_at' => now()->subMinutes(8),
    ]);

    // Seed a variety of posts that cover every branch of the dashboard visibility rules.
    $ownPostId = DB::table('posts')->insertGetId([
        'user_id' => $viewer->id,
        'content' => 'Daily training update',
        'posts_visibility' => 'public',
        'created_at' => now()->subMinutes(7),
        'updated_at' => now()->subMinutes(7),
    ]);

    $friendPostId = DB::table('posts')->insertGetId([
        'user_id' => $friend->id,
        'content' => 'Friend only update',
        'posts_visibility' => 'friends',
        'created_at' => now()->subMinutes(6),
        'updated_at' => now()->subMinutes(6),
    ]);

    $followedPostId = DB::table('posts')->insertGetId([
        'user_id' => $followed->id,
        'content' => 'Followed account update',
        'posts_visibility' => 'friends',
        'created_at' => now()->subMinutes(5),
        'updated_at' => now()->subMinutes(5),
    ]);

    $publicPostId = DB::table('posts')->insertGetId([
        'user_id' => $publicAuthor->id,
        'content' => 'General community post',
        'posts_visibility' => 'public',
        'created_at' => now()->subMinutes(4),
        'updated_at' => now()->subMinutes(4),
    ]);

    $blockedPostId = DB::table('posts')->insertGetId([
        'user_id' => $blocked->id,
        'content' => 'Blocked user broadcast',
        'posts_visibility' => 'public',
        'created_at' => now()->subMinutes(3),
        'updated_at' => now()->subMinutes(3),
    ]);

    $sharedBlockedPostId = DB::table('posts')->insertGetId([
        'user_id' => $blocked->id,
        'content' => 'Shared highlight from blocked user',
        'posts_visibility' => 'friends',
        'created_at' => now()->subMinutes(2),
        'updated_at' => now()->subMinutes(2),
    ]);

    // Authenticate the viewer before attaching share metadata to the feed.
    actingAs($viewer);

    // Share one of the blocked posts so the OR condition keeps it visible.
    DB::table('shares')->insert([
        'user_id' => $viewer->id,
        'post_id' => $sharedBlockedPostId,
        'created_at' => now()->subMinute(),
        'updated_at' => now()->subMinute(),
    ]);

    // Instantiate the Livewire component manually to interrogate its paginator payload.
    $component = app(UserDashboard::class);
    $component->mount();

    // Verify the posts property exposes a paginator with the expected page length.
    expect($component->posts)
        ->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($component->posts->perPage())->toBe(10);

    // Collect the ordered post identifiers returned by the dashboard query.
    $visiblePostIds = $component->posts->getCollection()->pluck('id')->all();

    // Validate that every permitted post surfaces in the feed while the unshared blocked post stays hidden.
    expect($visiblePostIds)
        ->toContain($ownPostId)
        ->toContain($friendPostId)
        ->toContain($followedPostId)
        ->toContain($publicPostId)
        ->toContain($sharedBlockedPostId)
        ->not->toContain($blockedPostId);

    // Reset the fake clock after the assertions complete to prevent cross-test bleed.
    Carbon::setTestNow();
});
