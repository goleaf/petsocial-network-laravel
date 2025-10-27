<?php

use App\Http\Livewire\Content\CommentSection;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    // Reset the database so pagination and edit state assertions operate on fresh data.
    prepareTestDatabase();
});

describe('Comment section Livewire behaviors', function () {
    it('supports editing existing comments and replying in a single session', function () {
        // Provision a user and post to anchor the comment thread within the database.
        $author = User::factory()->create(['name' => 'ThreadStarter']);
        $post = Post::create([
            'user_id' => $author->id,
            'content' => 'Initial post content',
        ]);

        // Seed an original comment authored by the acting user.
        $comment = Comment::create([
            'user_id' => $author->id,
            'post_id' => $post->id,
            'content' => 'Original thought',
        ]);

        // Authenticate as the author to exercise edit and reply capabilities.
        actingAs($author);

        // Drive the component through edit and reply interactions.
        Livewire::test(CommentSection::class, ['postId' => $post->id])
            ->call('edit', $comment->id)
            ->assertSet('editingCommentId', $comment->id)
            ->set('editingContent', 'Updated insight')
            ->call('update')
            ->assertSet('editingCommentId', null)
            ->assertSet('editingContent', '')
            ->call('reply', $comment->id)
            ->assertSet('replyingToId', $comment->id)
            ->set('content', 'A follow-up reply')
            ->call('save')
            ->assertSet('replyingToId', null);

        // Verify the original comment was updated and a reply was persisted with the correct linkage.
        expect($comment->fresh()->content)->toBe('Updated insight');

        $reply = Comment::where('parent_id', $comment->id)->first();
        expect($reply)->not->toBeNull();
        expect($reply->content)->toBe('A follow-up reply');
        expect($reply->post_id)->toBe($post->id);
    });

    it('prevents other users from editing comments they do not own', function () {
        // Create the original author and a separate viewer who will attempt the edit.
        $owner = User::factory()->create(['name' => 'OriginalOwner']);
        $viewer = User::factory()->create(['name' => 'CuriousViewer']);

        // Seed a post and comment owned by the original author.
        $post = Post::create([
            'user_id' => $owner->id,
            'content' => 'Ownership protected post',
        ]);
        $comment = Comment::create([
            'user_id' => $owner->id,
            'post_id' => $post->id,
            'content' => 'Author only content',
        ]);

        // Authenticate as the viewer who should be blocked from editing the protected comment.
        actingAs($viewer);

        // Trigger the edit action and assert the component keeps the edit state untouched.
        Livewire::test(CommentSection::class, ['postId' => $post->id])
            ->call('edit', $comment->id)
            ->assertSet('editingCommentId', null)
            ->assertSet('editingContent', '');

        // Confirm the underlying database record was not changed as part of the attempt.
        expect($comment->fresh()->content)->toBe('Author only content');
    });
});
