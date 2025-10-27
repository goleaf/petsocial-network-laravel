<?php

use App\Models\Friendship;
use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

it('recommends groups that share the members interests while excluding existing memberships', function (): void {
    // Reset cached lookups to ensure the discovery queries operate on fresh fixtures.
    Cache::flush();

    // Seed the viewer and two categories that represent distinct interests.
    $viewer = User::factory()->create();
    $creator = User::factory()->create();
    $adventureCategory = Category::query()->create([
        'name' => 'Adventure',
        'slug' => 'adventure',
        'is_active' => true,
    ]);
    $wellnessCategory = Category::query()->create([
        'name' => 'Wellness',
        'slug' => 'wellness',
        'is_active' => true,
    ]);

    // Join the viewer to one adventure group so their interests have signal.
    $joinedGroup = Group::query()->create([
        'name' => 'Trail Seekers',
        'description' => 'Explorers who love sunrise hikes.',
        'category_id' => $adventureCategory->id,
        'visibility' => Group::VISIBILITY_OPEN,
        'creator_id' => $creator->id,
    ]);
    $joinedGroup->members()->attach($viewer->id, [
        'role' => 'member',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    // Additional adventure groups should be surfaced by the recommendation engine.
    $recommendedOne = Group::query()->create([
        'name' => 'Summit Saturdays',
        'description' => 'Weekly challenges for intermediate climbers.',
        'category_id' => $adventureCategory->id,
        'visibility' => Group::VISIBILITY_OPEN,
        'creator_id' => $creator->id,
    ]);
    $recommendedTwo = Group::query()->create([
        'name' => 'Weekend Climbers',
        'description' => 'Pack your ropes and chalk for social ascents.',
        'category_id' => $adventureCategory->id,
        'visibility' => Group::VISIBILITY_CLOSED,
        'creator_id' => $creator->id,
    ]);

    // Secret groups and unrelated categories should never appear in the personalised list.
    Group::query()->create([
        'name' => 'Mystery Summit',
        'description' => 'Invite-only expeditions.',
        'category_id' => $adventureCategory->id,
        'visibility' => Group::VISIBILITY_SECRET,
        'creator_id' => $creator->id,
    ]);
    Group::query()->create([
        'name' => 'Mindful Moments',
        'description' => 'Meditation and mindfulness tips.',
        'category_id' => $wellnessCategory->id,
        'visibility' => Group::VISIBILITY_OPEN,
        'creator_id' => $creator->id,
    ]);

    $recommendations = Group::discoverByInterests($viewer, 5);

    expect($recommendations->pluck('id')->all())
        ->toContain($recommendedOne->id, $recommendedTwo->id)
        ->not->toContain($joinedGroup->id);
});

it('surfaces groups where the viewers friends are already active', function (): void {
    // Ensure cache does not contain stale friend-based recommendations.
    Cache::flush();

    // Build a viewer alongside two accepted friends and a third pending acquaintance.
    $viewer = User::factory()->create();
    $friendA = User::factory()->create();
    $friendB = User::factory()->create();
    $pendingFriend = User::factory()->create();
    $creator = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'Community Builders',
        'slug' => 'community-builders',
        'is_active' => true,
    ]);

    // Persist accepted friendships to populate the viewers social graph.
    Friendship::query()->create([
        'sender_id' => $viewer->id,
        'recipient_id' => $friendA->id,
        'status' => Friendship::STATUS_ACCEPTED,
        'accepted_at' => now(),
    ]);
    Friendship::query()->create([
        'sender_id' => $friendB->id,
        'recipient_id' => $viewer->id,
        'status' => Friendship::STATUS_ACCEPTED,
        'accepted_at' => now(),
    ]);
    Friendship::query()->create([
        'sender_id' => $viewer->id,
        'recipient_id' => $pendingFriend->id,
        'status' => Friendship::STATUS_PENDING,
    ]);

    // Create a group where both accepted friends are active members.
    $friendHub = Group::query()->create([
        'name' => 'Local Volunteers',
        'description' => 'Coordinate weekend service projects.',
        'category_id' => $category->id,
        'visibility' => Group::VISIBILITY_OPEN,
        'creator_id' => $creator->id,
    ]);
    $friendHub->members()->attach($friendA->id, [
        'role' => 'member',
        'status' => 'active',
        'joined_at' => now(),
    ]);
    $friendHub->members()->attach($friendB->id, [
        'role' => 'member',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    // Another group with only a pending friend should be excluded from recommendations.
    $pendingGroup = Group::query()->create([
        'name' => 'Future Friends Circle',
        'description' => 'Planning events but waiting on approvals.',
        'category_id' => $category->id,
        'visibility' => Group::VISIBILITY_OPEN,
        'creator_id' => $creator->id,
    ]);
    $pendingGroup->members()->attach($pendingFriend->id, [
        'role' => 'member',
        'status' => 'pending',
    ]);

    $recommendations = Group::discoverByConnections($viewer, 5);

    expect($recommendations->pluck('id')->all())
        ->toContain($friendHub->id)
        ->not->toContain($pendingGroup->id);

    // The recommendation should expose how many friends are already inside for ranking and UI badges.
    expect($recommendations->firstWhere('id', $friendHub->id)?->friend_members_count)
        ->toBe(2);
});
