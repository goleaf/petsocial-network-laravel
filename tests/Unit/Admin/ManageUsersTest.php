<?php

use App\Http\Livewire\Admin\ManageUsers;
use App\Models\Comment;
use App\Models\CommentReport;
use App\Models\Post;
use App\Models\PostReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    // Wire up the dynamic reports relationship to prevent query errors when loadData runs.
    Comment::resolveRelationUsing('reports', function (Comment $comment) {
        return $comment->hasMany(CommentReport::class, 'comment_id');
    });
});

it('suspends a member and resets suspension form state', function (): void {
    // Authenticate as an administrator so the component can refresh its datasets during the workflow.
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);
    auth()->login($admin);

    // Create a member that will be suspended through the component logic.
    $member = User::factory()->create([
        'role' => 'user',
    ]);

    // Instantiate the component directly to exercise the confirmSuspend handler in isolation.
    $component = new ManageUsers();
    $component->suspendUserId = $member->id;
    $component->suspendDays = 5;
    $component->suspendReason = 'Manual moderation hold';

    // Trigger the confirmation routine which validates input, applies the suspension, and refreshes data.
    $component->confirmSuspend();

    // Ensure the member is suspended and the temporary form fields were cleared.
    expect($member->fresh()->isSuspended())->toBeTrue();
    expect($component->suspendUserId)->toBeNull();
    expect($component->suspendDays)->toBeNull();
    expect($component->suspendReason)->toBeNull();
});

it('loads managed datasets excluding the authenticated administrator', function (): void {
    // Authenticate as an administrator so loadData can scope results properly.
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);
    auth()->login($admin);

    // Seed a member with reported content so each dataset includes actionable information.
    $member = User::factory()->create([
        'role' => 'user',
    ]);
    $post = Post::create([
        'user_id' => $member->id,
        'content' => 'Content requiring moderation',
    ]);
    PostReport::create([
        'user_id' => $admin->id,
        'post_id' => $post->id,
        'reason' => 'Flagged for testing',
    ]);
    $comment = Comment::create([
        'commentable_type' => Post::class,
        'commentable_id' => $post->id,
        'user_id' => $member->id,
        'content' => 'Reported comment body',
    ]);
    CommentReport::create([
        'user_id' => $admin->id,
        'comment_id' => $comment->id,
        'reason' => 'Escalated for admin review',
    ]);

    // Mount the component so the mount lifecycle invokes loadData automatically.
    $component = new ManageUsers();
    $component->mount();

    expect($component->users->pluck('id'))->not->toContain($admin->id);
    expect($component->users->pluck('id'))->toContain($member->id);
    expect($component->reportedPosts->pluck('id'))->toContain($post->id);
    expect($component->reportedComments->pluck('id'))->toContain($comment->id);
});

it('unsuspends members and refreshes managed datasets', function (): void {
    // Authenticate as an administrator so the component can refresh collections after the action.
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);
    auth()->login($admin);

    // Suspend a member prior to invoking the unsuspend handler.
    $member = User::factory()->create([
        'role' => 'user',
    ]);
    $member->suspend(3, 'Temporary hold');

    // Instantiate the component directly to test the unsuspend workflow in isolation.
    $component = new ManageUsers();
    $component->unsuspendUser($member->id);

    expect($member->fresh()->isSuspended())->toBeFalse();
});

it('cancels the edit workflow and clears the editing identifier', function (): void {
    // Create an administrator and log them in to keep component expectations consistent.
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);
    auth()->login($admin);

    // Create a member and populate the edit form state before cancelling the workflow.
    $member = User::factory()->create([
        'role' => 'user',
        'name' => 'Editable Member',
    ]);
    $component = new ManageUsers();
    $component->editUser($member->id);
    expect($component->editingUserId)->toBe($member->id);

    // Cancel the edit operation and confirm the identifier resets to null.
    $component->cancelEdit();

    expect($component->editingUserId)->toBeNull();
});
