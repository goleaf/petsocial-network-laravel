<?php

use App\Http\Livewire\Content\ReactionButton;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;
use Livewire\Livewire;

/**
 * Livewire-level checks for the reaction button component methods.
 */
it('removes an existing reaction when the same emoji is selected again', function () {
    // Establish the author, reacting user, and a post for the toggle scenario.
    $author = User::factory()->create();
    $viewer = User::factory()->create();

    $post = Post::create([
        'user_id' => $author->id,
        'content' => 'Toggling a reaction should remove it cleanly.',
    ]);

    // Pre-seed the database with a like reaction to ensure the toggle path is exercised.
    Reaction::create([
        'user_id' => $viewer->id,
        'post_id' => $post->id,
        'type' => 'like',
    ]);

    $this->actingAs($viewer);

    Livewire::test(ReactionButton::class, ['postId' => $post->id])
        ->call('react', 'like')
        ->assertSet('currentReaction', null);

    // Confirm the reaction table no longer has an entry for the user and post pair.
    $this->assertDatabaseMissing('reactions', [
        'user_id' => $viewer->id,
        'post_id' => $post->id,
    ]);
});

it('updates the stored reaction when a different emoji is chosen', function () {
    // Create the surrounding context so the reaction update path can run.
    $author = User::factory()->create();
    $viewer = User::factory()->create();

    $post = Post::create([
        'user_id' => $author->id,
        'content' => 'Users can change their mind and pick a new reaction.',
    ]);

    Reaction::create([
        'user_id' => $viewer->id,
        'post_id' => $post->id,
        'type' => 'sad',
    ]);

    $this->actingAs($viewer);

    Livewire::test(ReactionButton::class, ['postId' => $post->id])
        ->call('react', 'haha')
        ->assertSet('currentReaction', 'haha');

    // Validate that the reaction record reflects the updated emoji choice.
    $this->assertDatabaseHas('reactions', [
        'user_id' => $viewer->id,
        'post_id' => $post->id,
        'type' => 'haha',
    ]);
});

it('ignores invalid reaction types and keeps the current selection untouched', function () {
    // Prepare the reacting user and a stored reaction so we can validate the guard branch.
    $author = User::factory()->create();
    $viewer = User::factory()->create();

    $post = Post::create([
        'user_id' => $author->id,
        'content' => 'Invalid reaction keys should be rejected without errors.',
    ]);

    Reaction::create([
        'user_id' => $viewer->id,
        'post_id' => $post->id,
        'type' => 'wow',
    ]);

    $this->actingAs($viewer);

    Livewire::test(ReactionButton::class, ['postId' => $post->id])
        ->call('react', 'made-up')
        ->assertSet('currentReaction', 'wow');

    // Ensure the database still reflects the original reaction choice after the invalid attempt.
    $this->assertDatabaseHas('reactions', [
        'user_id' => $viewer->id,
        'post_id' => $post->id,
        'type' => 'wow',
    ]);
});
