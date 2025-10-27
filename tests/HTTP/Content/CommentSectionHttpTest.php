<?php

use App\Http\Livewire\Content\CommentSection;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    // Refresh the SQLite schema so the Livewire route has the necessary tables available.
    prepareTestDatabase();
});

it('responds with the Livewire markup when the comment section is routed over HTTP', function () {
    // Establish a signed-in user and backing post so the Livewire route can resolve cleanly.
    $user = User::factory()->create(['name' => 'HttpUser']);
    $post = Post::create([
        'user_id' => $user->id,
        'content' => 'Route level post',
    ]);

    actingAs($user);

    // Register a temporary route that points directly at the Livewire component as Filament would.
    Route::middleware('web')->get('/testing/posts/{postId}/comments', CommentSection::class)->name('testing.comments');

    // Execute a standard HTTP request to confirm the component renders without Livewire test helpers.
    $response = get('/testing/posts/'.$post->id.'/comments');

    $response->assertOk();
    $response->assertSee('<textarea', false);
    $response->assertSee('wire:submit.prevent="save"', false);
});

it('renders persisted comments with action controls inside the HTTP response', function () {
    // Seed the post author and a comment so the Livewire blade can display existing content.
    $author = User::factory()->create(['name' => 'MarkupOwner']);
    $post = Post::create([
        'user_id' => $author->id,
        'content' => 'Post with existing comments',
    ]);
    $comment = $post->comments()->create([
        'user_id' => $author->id,
        'content' => 'Visible via HTTP request',
    ]);

    // Authenticate as the author to simplify authorization for the delete/edit controls.
    actingAs($author);

    // Route the component through the web middleware stack just like the production entry point.
    Route::middleware('web')->get('/testing/posts/{postId}/comments', CommentSection::class);

    // Issue a GET request and ensure the rendered markup contains controls for the seeded comment.
    $response = get('/testing/posts/'.$post->id.'/comments');

    $response->assertOk();
    $response->assertSee('wire:click="edit('.$comment->id.')"', false);
    $response->assertSee('wire:click="delete('.$comment->id.')"', false);
    $response->assertSee('wire:click="reply('.$comment->id.')"', false);
});
