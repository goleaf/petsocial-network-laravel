<?php

use App\Http\Livewire\Common\CommentManager;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ViewErrorBag;
use function Pest\Laravel\actingAs;

it('exposes the comment manager markup over HTTP for embed scenarios', function (): void {
    // Allow comment creation so the rendered HTML includes meaningful content to assert against.
    Comment::unguard();

    // Build a post with an associated comment authored by the signed-in user.
    $viewer = User::factory()->create(['name' => 'viewer']);
    $post = Post::create([
        'user_id' => $viewer->id,
        'content' => 'HTTP integration post',
    ]);
    Comment::create([
        'user_id' => $viewer->id,
        'post_id' => $post->id,
        'content' => 'Rendered over HTTP',
    ]);

    // Register a lightweight route that boots the Livewire component manually and returns its rendered view.
    Route::get('/test-comment-manager', function () use ($post) {
        // Instantiate the component and invoke mount() so the internal state mirrors a Livewire request.
        $component = app(CommentManager::class);
        $component->mount($post->id);

        // Return the rendered Blade view to simulate embedding within a controller response.
        return $component->render()->with([
            'replyingToId' => $component->replyingToId,
            'editingCommentId' => $component->editingCommentId,
            'editingContent' => $component->editingContent,
            'content' => $component->content,
            'errors' => new ViewErrorBag,
        ]);
    });

    // Act as the viewer to satisfy the auth guard and request the synthetic endpoint.
    actingAs($viewer)
        ->get('/test-comment-manager')
        ->assertOk()
        ->assertSee('Rendered over HTTP');

    // Reset guarding after the response is validated.
    Comment::reguard();
});
