<?php

use App\Http\Livewire\Admin\ManageUsers;
use App\Models\Comment;
use App\Models\CommentReport;
use App\Models\Post;
use App\Models\PostReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

use function Pest\Laravel\actingAs;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    // Provide the reports relationship so Livewire can pull comment data without hitting missing relation exceptions.
    Comment::resolveRelationUsing('reports', function (Comment $comment) {
        return $comment->hasMany(CommentReport::class, 'comment_id');
    });
});

it('updates user information through the Livewire admin workflow', function (): void {
    // Authenticate as an administrator to access the component lifecycle.
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);
    actingAs($admin);

    // Create a member whose profile will be edited through the Livewire actions.
    $member = User::factory()->create([
        'role' => 'user',
        'name' => 'Original Name',
        'email' => 'original@example.com',
    ]);

    // Drive the edit workflow, update the fields, and persist the changes via the component.
    Livewire::test(ManageUsers::class)
        ->call('editUser', $member->id)
        ->set('editName', 'Updated Name')
        ->set('editEmail', 'updated@example.com')
        ->set('editRole', 'moderator')
        ->call('updateUser')
        ->assertHasNoErrors()
        ->assertSet('editingUserId', null);

    // Confirm the member model reflects the requested updates and new role assignment.
    expect($member->fresh()->only(['name', 'email', 'role']))->toMatchArray([
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'role' => 'moderator',
    ]);
});

it('opens the suspension modal when an administrator selects a member', function (): void {
    // Authenticate as an administrator so component lifecycle hooks can access the current user.
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);
    actingAs($admin);

    // Create a regular member who will be targeted by the suspension initiation action.
    $member = User::factory()->create([
        'role' => 'user',
    ]);

    // Trigger the suspendUser handler and confirm the modal tracking property updates accordingly.
    Livewire::test(ManageUsers::class)
        ->call('suspendUser', $member->id)
        ->assertSet('suspendUserId', $member->id);
});

it('removes reported content through administrative actions', function (): void {
    // Authenticate as an administrator to mount the component with moderation datasets.
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);
    actingAs($admin);

    // Create a member with reported post and comment content to exercise deletion handlers.
    $member = User::factory()->create([
        'role' => 'user',
    ]);
    $post = Post::create([
        'user_id' => $member->id,
        'content' => 'Reported post body',
    ]);
    PostReport::create([
        'user_id' => $admin->id,
        'post_id' => $post->id,
        'reason' => 'Policy violation',
    ]);
    $comment = Comment::create([
        'commentable_type' => Post::class,
        'commentable_id' => $post->id,
        'user_id' => $member->id,
        'content' => 'Reported comment text',
    ]);
    CommentReport::create([
        'user_id' => $admin->id,
        'comment_id' => $comment->id,
        'reason' => 'Spam reply',
    ]);

    // Issue deletion calls for both the reported post and comment and verify the records are removed.
    Livewire::test(ManageUsers::class)
        ->call('deletePost', $post->id)
        ->call('deleteComment', $comment->id);

    expect(Post::find($post->id))->toBeNull();
    expect(Comment::find($comment->id))->toBeNull();
});
