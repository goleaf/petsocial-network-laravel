<?php

use App\Http\Livewire\Content\CommentSection;
use App\Models\ActivityLog;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    // Rebuild the in-memory SQLite schema so every scenario starts with pristine tables.
    prepareTestDatabase();
});

describe('Comment section feature interactions', function () {
    it('stores a new comment, notifies the post owner, and logs the activity', function () {
        // Fake notifications so the assertions can inspect the payloads deterministically.
        Notification::fake();

        // Create a post owner who should receive the comment notification.
        $owner = User::factory()->create(['name' => 'OwnerUser']);

        // Persist a post so the Livewire component has a target model to load comments for.
        $post = Post::create([
            'user_id' => $owner->id,
            'content' => 'A wholesome pet update',
        ]);

        // Seed an additional user who will be mentioned inside the comment body.
        $mentioned = User::factory()->create(['name' => 'MentionBuddy']);

        // Authenticate as the commenting user who will submit the Livewire form.
        $commentAuthor = User::factory()->create(['name' => 'CommentHero']);
        actingAs($commentAuthor);

        // Drive the component to submit a new comment containing a mention token.
        Livewire::test(CommentSection::class, ['postId' => $post->id])
            ->set('content', 'Great story @MentionBuddy!')
            ->call('save')
            ->assertSet('content', '')
            ->assertSet('replyingToId', null);

        // Ensure the comment persisted exactly once and matches the expected payload.
        $comment = Comment::first();
        expect(Comment::count())->toBe(1);
        expect($comment->post_id)->toBe($post->id);
        expect($comment->content)->toBe('Great story @MentionBuddy!');
        expect($comment->parent_id)->toBeNull();

        // Confirm the post owner was notified about the comment event.
        Notification::assertSentTo($owner, ActivityNotification::class, function (ActivityNotification $notification) use ($commentAuthor, $post) {
            // The notification should describe a comment and originate from the comment author.
            expect($notification->type)->toBe('comment');
            expect($notification->fromUser->is($commentAuthor))->toBeTrue();
            expect($notification->post->is($post))->toBeTrue();

            return true;
        });

        // Confirm the mentioned user received a mention notification from the same action.
        Notification::assertSentTo($mentioned, ActivityNotification::class, function (ActivityNotification $notification) use ($commentAuthor, $post) {
            expect($notification->type)->toBe('mention');
            expect($notification->fromUser->is($commentAuthor))->toBeTrue();
            expect($notification->post->is($post))->toBeTrue();

            return true;
        });

        // Finally, the activity log should contain the standardized metadata for analytics.
        $activity = ActivityLog::first();
        expect($activity)->not->toBeNull();
        expect($activity->action)->toBe('comment_added');
        expect($activity->metadata['post_id'])->toBe($post->id);
        expect($activity->metadata['comment_id'])->toBe($comment->id);
        expect($activity->metadata['preview'])->toBe('Great story @MentionBuddy!');
    });

    it('allows the author to delete a comment and records the deletion log entry', function () {
        // Authenticate as a user who will own the comment being removed.
        $author = User::factory()->create(['name' => 'DeleteCaptain']);
        actingAs($author);

        // Create a post so the Livewire component has a parent context to mount against.
        $post = Post::create([
            'user_id' => $author->id,
            'content' => 'A temporary thought for deletion',
        ]);

        // Persist a comment owned by the authenticated user to exercise the delete branch.
        $comment = Comment::create([
            'user_id' => $author->id,
            'post_id' => $post->id,
            'content' => 'This comment will be removed',
        ]);

        // Execute the Livewire delete action which should remove the record and log the event.
        Livewire::test(CommentSection::class, ['postId' => $post->id])
            ->call('delete', $comment->id);

        // Confirm the comment no longer exists in the database after the operation.
        expect(Comment::whereKey($comment->id)->exists())->toBeFalse();

        // Ensure the activity log captured the standardized deletion metadata for analytics.
        $activity = ActivityLog::latest()->first();
        expect($activity)->not->toBeNull();
        expect($activity->action)->toBe('comment_deleted');
        expect($activity->metadata['post_id'])->toBe($post->id);
        expect($activity->metadata['comment_id'])->toBe($comment->id);
    });
});
