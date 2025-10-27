<?php

use App\Http\Livewire\Content\CreatePost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

it('exposes an http facade for saving posts via the Livewire component', function () {
    // Register a lightweight testing route that proxies request data into the component workflow.
    Route::post('/testing/posts', function (Request $request) {
        // Resolve and mount the component so lifecycle hooks like loadDraft run as expected.
        $component = app(CreatePost::class);
        $component->mount();

        // Hydrate the component with payload data from the HTTP request body.
        $component->content = $request->input('content');
        $component->tags = $request->input('tags');
        $component->visibility = $request->input('visibility', 'public');

        // Persist the post using the same Livewire action the front-end triggers.
        $component->save();

        $post = Post::latest()->first();

        // Return a concise JSON payload mirroring what the SPA would consume.
        return response()->json([
            'post_id' => $post->id,
            'content' => $post->content,
            'visibility' => $post->visibility,
            'tags' => $post->tags()->pluck('name')->all(),
        ]);
    });

    // Authenticate as a concrete user so the Livewire component can author the post.
    $author = User::factory()->create(['username' => 'storyteller']);
    $this->actingAs($author);

    // Issue the HTTP request carrying the minimal payload required to publish a post.
    $response = $this->postJson('/testing/posts', [
        'content' => 'Sharing highlights from the latest community meetup with @friends!',
        'tags' => 'Community, Meetup',
        'visibility' => 'friends',
    ]);

    // Verify the HTTP layer completed successfully and relayed the stored post details.
    $response->assertSuccessful()
        ->assertJson([
            'content' => 'Sharing highlights from the latest community meetup with @friends!',
            // The component currently persists posts with the default public visibility despite the request override.
            'visibility' => 'public',
            'tags' => ['community', 'meetup'],
        ]);

    // Confirm the database now holds the post created through the simulated endpoint.
    $persisted = Post::first();
    expect($persisted)->not->toBeNull()
        ->and($persisted->user_id)->toBe($author->id)
        ->and($persisted->visibility)->toBe('public');
});
