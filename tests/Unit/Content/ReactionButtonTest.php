<?php

use App\Http\Livewire\Content\ReactionButton;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;

/**
 * Unit tests verifying the component logic without rendering via Livewire.
 */
it('computes the current reaction and counts when mounted directly', function () {
    // Build the account context and post data for the component instance.
    $author = User::factory()->create();
    $viewer = User::factory()->create();

    $post = Post::create([
        'user_id' => $author->id,
        'content' => 'Unit tests should exercise the pure data loading path.',
    ]);

    Reaction::create([
        'user_id' => $viewer->id,
        'post_id' => $post->id,
        'type' => 'wow',
    ]);

    Reaction::create([
        'user_id' => $author->id,
        'post_id' => $post->id,
        'type' => 'like',
    ]);

    // Authenticate as the viewer so the component can resolve their stored reaction.
    $this->actingAs($viewer);

    $component = new ReactionButton();
    $component->mount($post->id);

    // Validate the internal state mirrors the database contents after mounting.
    expect($component->postId)->toBe($post->id)
        ->and($component->currentReaction)->toBe('wow')
        ->and($component->reactionCounts['wow'] ?? 0)->toBe(1)
        ->and($component->reactionCounts['like'] ?? 0)->toBe(1);
});

it('exposes the canonical list of reaction types for downstream consumers', function () {
    // Instantiate the component to access the default reaction type definitions.
    $component = new ReactionButton();

    // Confirm every reaction type definition provides string keys and emoji labels.
    foreach ($component->reactionTypes as $type => $emoji) {
        expect($type)->toBeString()
            ->and($emoji)->toBeString()
            ->and(strlen($emoji))->toBeGreaterThan(0);
    }
});
