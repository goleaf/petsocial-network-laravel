<?php

use App\Http\Livewire\Common\CommentManager;
use App\Models\ActivityLog;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    // Allow mass-assignment on the Comment model so Livewire can persist payloads during tests.
    Comment::unguard();

    // Provide a no-op emit() macro so legacy event dispatches in the component do not error under Livewire v3.
    if (! Component::hasMacro('emit')) {
        Component::macro('emit', function (): Component {
            return $this;
        });
    }
});

afterEach(function (): void {
    // Restore mass-assignment protection to avoid leaking the relaxed state into other suites.
    Comment::reguard();
});

it('saves a comment, records an activity log, and notifies relevant users', function (): void {
    // Fake notifications to inspect which notifiable entities receive comment updates.
    Notification::fake();

    // Create the post owner, the authenticated commenter, and a mentioned friend with slug-friendly names for regex parsing.
    $postOwner = User::factory()->create(['name' => 'owner']);
    $commentAuthor = User::factory()->create(['name' => 'commenter']);
    $mentionedFriend = User::factory()->create(['name' => 'buddy']);

    // Persist a post that belongs to the owner so the component can resolve the relationship in mount().
    $post = Post::create([
        'user_id' => $postOwner->id,
        'content' => 'Original post body',
    ]);

    // Seed cache entries that should be invalidated after the comment is stored.
    Cache::put("post_{$post->id}_comments", collect());
    Cache::put("post_{$post->id}_comments_count", 99);
    Cache::put("post_{$post->id}_top_comments", collect());

    // Authenticate as the commenter to satisfy the component's auth() calls while submitting content with a mention.
    actingAs($commentAuthor);

    // Hydrate the Livewire component, fill in the body, and trigger the save action.
    Livewire::test(CommentManager::class, ['postId' => $post->id])
        ->set('content', 'First thought for @buddy to read')
        ->call('save')
        ->assertSet('content', '')
        ->assertSet('replyingToId', null);

    // Confirm the comment record exists with the authored content.
    expect(Comment::query()->count())->toBe(1)
        ->and(Comment::first()->content)->toBe('First thought for @buddy to read');

    // Ensure the owner and mentioned friend both received the expected activity notifications.
    Notification::assertSentToTimes($postOwner, ActivityNotification::class, 1);
    Notification::assertSentToTimes($mentionedFriend, ActivityNotification::class, 1);

    // Confirm an activity log entry captured the creation event metadata.
    expect(ActivityLog::query()->count())->toBe(1)
        ->and(ActivityLog::first()->description)->toContain('Commented on post ID '.$post->id);

});

it('updates an existing comment and clears cached fragments', function (): void {
    // Authenticate a comment author responsible for both the post and the feedback thread.
    $author = User::factory()->create(['name' => 'author']);

    // Create the post and an initial comment so the component has content to edit.
    $post = Post::create([
        'user_id' => $author->id,
        'content' => 'Original post body',
    ]);
    $comment = Comment::create([
        'user_id' => $author->id,
        'post_id' => $post->id,
        'content' => 'Pending revision text',
    ]);

    // Prime cache keys that should be invalidated when update() calls clearCommentsCache().
    Cache::put("post_{$post->id}_comments", collect(['stale']));
    Cache::put("post_{$post->id}_comments_count", 22);
    Cache::put("post_{$post->id}_top_comments", collect(['stale']));

    // Act as the author so edit() scopes the lookup correctly to their comment record.
    actingAs($author);

    // Drive the Livewire component through edit() and update() to mutate the stored body text.
    Livewire::test(CommentManager::class, ['postId' => $post->id])
        ->call('edit', $comment->id)
        ->set('editingContent', 'Updated insight with clearer context')
        ->call('update')
        ->assertSet('editingCommentId', null)
        ->assertSet('editingContent', '');

    // Validate the underlying comment reflects the updated body copy.
    expect(Comment::find($comment->id)->content)->toBe('Updated insight with clearer context');

    // Ensure the cache fragments were purged so future renders receive the fresh data.
    expect(Cache::has("post_{$post->id}_comments"))->toBeFalse()
        ->and(Cache::has("post_{$post->id}_comments_count"))->toBeFalse()
        ->and(Cache::has("post_{$post->id}_top_comments"))->toBeFalse();

    // Confirm the activity log tracked the update event for auditing purposes.
    expect(ActivityLog::query()->count())->toBe(1)
        ->and(ActivityLog::first()->description)->toContain('Updated comment ID');
});

it('deletes a comment and records the removal event', function (): void {
    // Provision a commenter and their associated post so the component can perform the deletion.
    $author = User::factory()->create(['name' => 'author']);
    $post = Post::create([
        'user_id' => $author->id,
        'content' => 'Delete scenario post',
    ]);
    $comment = Comment::create([
        'user_id' => $author->id,
        'post_id' => $post->id,
        'content' => 'Comment scheduled for deletion',
    ]);

    // Seed cache keys to verify clearCommentsCache() executes during the delete flow as well.
    Cache::put("post_{$post->id}_comments", collect(['stale']));
    Cache::put("post_{$post->id}_comments_count", 7);
    Cache::put("post_{$post->id}_top_comments", collect(['stale']));

    // Authenticate as the author so the ownership guard passes when calling delete().
    actingAs($author);

    // Invoke the Livewire delete action to remove the record from persistence.
    Livewire::test(CommentManager::class, ['postId' => $post->id])
        ->call('delete', $comment->id);

    // The comment table should no longer contain the deleted record.
    expect(Comment::query()->count())->toBe(0);

    // Cache fragments should be flushed to avoid exposing stale comment counts.
    expect(Cache::has("post_{$post->id}_comments"))->toBeFalse()
        ->and(Cache::has("post_{$post->id}_comments_count"))->toBeFalse()
        ->and(Cache::has("post_{$post->id}_top_comments"))->toBeFalse();

    // Activity logging must capture the deletion for moderation transparency.
    expect(ActivityLog::query()->count())->toBe(1)
        ->and(ActivityLog::first()->description)->toContain('Deleted comment ID');
});
