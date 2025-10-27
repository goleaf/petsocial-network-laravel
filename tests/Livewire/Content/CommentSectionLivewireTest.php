<?php

use App\Http\Livewire\Content\CommentSection;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

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
});
