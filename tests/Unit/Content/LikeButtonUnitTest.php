<?php

use App\Http\Livewire\Content\LikeButton;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use function Pest\Laravel\actingAs;

it('hydrates component state during mount using authenticated context', function (): void {
    /**
     * Seed a post that is already liked by the acting user.
     */
    $user = User::factory()->create();
    $post = Post::query()->create([
        'user_id' => $user->id,
        'content' => 'Mount should detect this like automatically.',
    ]);
    Like::query()->create([
        'user_id' => $user->id,
        'post_id' => $post->id,
    ]);

    /**
     * Simulate the authenticated session and manually boot the component lifecycle.
     */
    actingAs($user);
    $component = new LikeButton();
    $component->mount($post->id);

    /**
     * Confirm the instance reflects the stored like state immediately after mounting.
     */
    expect($component->postId)->toBe($post->id);
    expect($component->isLiked)->toBeTrue();
    expect($component->likeCount)->toBe(1);
});
