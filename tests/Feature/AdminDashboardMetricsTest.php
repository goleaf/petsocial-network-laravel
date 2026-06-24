<?php

use App\Http\Livewire\Admin\Dashboard;
use App\Models\Comment;
use App\Models\CommentReport;
use App\Models\FriendRequest;
use App\Models\Message;
use App\Models\Pet;
use App\Models\PetActivity;
use App\Models\Post;
use App\Models\PostReport;
use App\Models\Reaction;
use App\Models\Report;
use App\Models\Share;
use App\Models\User;
use App\Models\UserActivity;
use Carbon\Carbon;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

it('aggregates dashboard metrics for administrators', function () {
    // Freeze time so relative date assertions remain stable.
    Carbon::setTestNow(Carbon::parse('2025-04-15 10:00:00'));

    // Create an administrator to access the dashboard features.
    $admin = User::withoutEvents(fn () => User::factory()->create(['role' => 'admin']));
    actingAs($admin);

    // Seed additional users representing recent signups and legacy members.
    $freshUser = User::withoutEvents(fn () => User::factory()->create(['created_at' => now()]));
    $legacyUser = User::withoutEvents(fn () => User::factory()->create(['created_at' => now()->subDays(5)]));
    $suspendedUser = User::withoutEvents(fn () => User::factory()->create([
        'created_at' => now()->subDays(10),
        'suspended_at' => now()->subDay(),
        'suspension_ends_at' => null,
        'suspension_reason' => 'Inappropriate content',
    ]));

    // Record friendship approvals in both directions so the halving logic can be asserted.
    FriendRequest::create([
        'sender_id' => $freshUser->id,
        'receiver_id' => $legacyUser->id,
        'status' => 'accepted',
    ]);
    FriendRequest::create([
        'sender_id' => $legacyUser->id,
        'receiver_id' => $freshUser->id,
        'status' => 'accepted',
    ]);

    // Provide a pet with activity so the leaderboard has meaningful data.
    $topPet = Pet::factory()->for($freshUser)->create();
    PetActivity::create([
        'pet_id' => $topPet->id,
        'type' => 'walk',
        'description' => 'Morning park stroll',
        'happened_at' => now(),
    ]);

    // Populate posts, reactions, shares, and comments to drive engagement metrics.
    $post = Post::unguarded(fn () => Post::create([
        'user_id' => $freshUser->id,
        'content' => 'Today was a great day at the dog park!',
        'created_at' => now(),
        'updated_at' => now(),
    ]));
    Post::unguarded(fn () => Post::create([
        'user_id' => $legacyUser->id,
        'content' => 'Throwback to last week',
        'created_at' => now()->subDays(7),
        'updated_at' => now()->subDays(7),
    ]));

    // Attach engagement records to highlight which users are the most active contributors.
    Reaction::create([
        'user_id' => $legacyUser->id,
        'post_id' => $post->id,
        'type' => 'like',
    ]);
    Share::create([
        'user_id' => $legacyUser->id,
        'post_id' => $post->id,
    ]);
    $comment = Comment::unguarded(fn () => Comment::create([
        'user_id' => $legacyUser->id,
        'post_id' => $post->id,
        'commentable_type' => Post::class,
        'commentable_id' => $post->id,
        'content' => 'Love this update!',
        'created_at' => now(),
        'updated_at' => now(),
    ]));

    // Register reports for moderation queues across posts, comments, and users.
    PostReport::create([
        'user_id' => $admin->id,
        'post_id' => $post->id,
        'reason' => 'Spam flag to review',
    ]);
    CommentReport::create([
        'user_id' => $admin->id,
        'comment_id' => $comment->id,
        'reason' => 'Tone check',
    ]);
    Report::unguarded(fn () => Report::create([
        'user_id' => $admin->id,
        'reportable_type' => User::class,
        'reportable_id' => $legacyUser->id,
        'reason' => 'Investigate repeated reports',
        'status' => Report::STATUS_PENDING,
        'created_at' => now(),
        'updated_at' => now(),
    ]));

    // Capture private messages to ensure message totals increment accordingly.
    Message::create([
        'sender_id' => $freshUser->id,
        'receiver_id' => $legacyUser->id,
        'content' => 'Let us coordinate the next meetup.',
    ]);

    // Store activities so the "active users today" statistic has dedicated entries.
    UserActivity::create([
        'user_id' => $freshUser->id,
        'type' => 'post_created',
        'description' => 'Posted about the latest adventure.',
    ]);
    UserActivity::create([
        'user_id' => $legacyUser->id,
        'type' => 'commented',
        'description' => 'Joined the conversation.',
    ]);

    // Execute the Livewire component to capture the computed data snapshot.
    $component = Livewire::test(Dashboard::class)
        // Validate the Livewire component is wired to the expected Blade view before inspecting state.
        ->assertViewIs('livewire.admin.dashboard');

    // Validate headline metrics reflecting total counts across primary entities.
    $component->assertSet('totalUsers', 4);
    $component->assertSet('totalPets', 1);
    $component->assertSet('totalPosts', 2);
    $component->assertSet('totalComments', 1);
    $component->assertSet('totalReactions', 1);
    $component->assertSet('totalShares', 1);
    $component->assertSet('totalFriendships', 1);
    $component->assertSet('totalMessages', 1);

    // Confirm daily activity statistics capture the frozen-time contributions.
    $component->assertSet('newUsersToday', 2);
    $component->assertSet('newPostsToday', 1);
    $component->assertSet('activeUsersToday', 2);

    // Ensure leaderboards and moderation panels surface the seeded records.
    $topUserIds = $component->get('topUsers')->pluck('id')->all();
    expect($topUserIds[0])->toBe($legacyUser->id);
    expect($topUserIds)->toContain($freshUser->id);
    expect($component->get('topPets')->first()->id)->toBe($topPet->id);
    expect($component->get('reportedPosts')->first()->id)->toBe($post->id);
    expect($component->get('reportedComments')->first()->id)->toBe($comment->id);
    expect($component->get('reportedUsers')->first()->id)->toBe($legacyUser->id);

    // Verify the suspended list and recent user feed include the dedicated records.
    expect($component->get('suspendedUsers')->pluck('id')->all())->toContain($suspendedUser->id);
    expect($component->get('recentUsers')->pluck('id')->all())->toContain($freshUser->id);

    // Reset the mocked time to avoid leaking state across unrelated tests.
    Carbon::setTestNow();
});
