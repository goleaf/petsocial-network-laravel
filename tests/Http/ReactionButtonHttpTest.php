<?php

use App\Http\Livewire\Content\ReactionButton;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\Fluent\AssertableJson;

/**
 * HTTP route tests that proxy to the reaction component for stateless integrations.
 */
it('allows an http endpoint to proxy reaction creation', function () {
    // Stand up the user, author, and post so the route closure can reuse component logic.
    $author = User::factory()->create();
    $viewer = User::factory()->create();

    $post = Post::create([
        'user_id' => $author->id,
        'content' => 'HTTP adapters can delegate to the Livewire component for reactions.',
    ]);

    // Define a lightweight route that leverages the component to record a reaction via HTTP.
    Route::post('/testing/react/{post}/{type}', function (Post $post, string $type) {
        $component = new ReactionButton();
        $component->mount($post->id);
        $component->react($type);

        return response()->json([
            'currentReaction' => $component->currentReaction,
            'counts' => $component->reactionCounts,
        ]);
    })->middleware('web')->name('testing.react');

    $this->actingAs($viewer);

    $response = $this->postJson("/testing/react/{$post->id}/angry");

    // Ensure the proxy call succeeded and returned the component state payload.
    $response->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('currentReaction', 'angry')
            ->has('counts.angry'));

    // Double-check the reaction persisted in the database for the acting user.
    $this->assertDatabaseHas('reactions', [
        'user_id' => $viewer->id,
        'post_id' => $post->id,
        'type' => 'angry',
    ]);
});
