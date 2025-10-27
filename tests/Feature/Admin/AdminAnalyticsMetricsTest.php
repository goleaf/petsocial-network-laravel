<?php

use App\Http\Livewire\Admin\Analytics;
use App\Models\FriendRequest;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\Share;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

/**
 * Feature coverage that mirrors the full admin analytics dashboard request lifecycle.
 */
it('summarizes network activity for administrators', function () {
    // Promote one user to an administrator so the admin middleware and metrics align with production usage.
    $admin = User::factory()->create(['role' => 'admin']);

    // Seed a handful of members to populate the aggregate counters.
    $powerUser = User::factory()->create();
    $socialUser = User::factory()->create();
    $quietUser = User::factory()->create();

    // Publish content and interactions for the dataset being summarised.
    $popularPost = Post::create([
        'content' => 'Weekly adoption success stories',
        'user_id' => $powerUser->id,
    ]);

    $supportingPost = Post::create([
        'content' => 'Community volunteer schedule',
        'user_id' => $socialUser->id,
    ]);

    DB::table('comments')->insert([
        'user_id' => $socialUser->id,
        'post_id' => $popularPost->id,
        'content' => 'Count me in for next week!',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Reaction::create([
        'user_id' => $socialUser->id,
        'post_id' => $popularPost->id,
        'type' => 'like',
    ]);

    Share::create([
        'user_id' => $quietUser->id,
        'post_id' => $popularPost->id,
    ]);

    // Accepted friend relationships are stored bidirectionally, so populate both directions.
    FriendRequest::create([
        'sender_id' => $powerUser->id,
        'receiver_id' => $socialUser->id,
        'status' => 'accepted',
    ]);

    FriendRequest::create([
        'sender_id' => $socialUser->id,
        'receiver_id' => $powerUser->id,
        'status' => 'accepted',
    ]);

    $this->actingAs($admin);

    $component = Livewire::test(Analytics::class);

    // Confirm the computed totals cover users, posts, comments, reactions, shares, and accepted friendships.
    $component
        ->assertSet('userCount', 4)
        ->assertSet('postCount', 2)
        ->assertSet('commentCount', 1)
        ->assertSet('reactionCount', 1)
        ->assertSet('shareCount', 1)
        ->assertSet('friendCount', 1);
});
