<?php

use App\Http\Livewire\Content\LikeButton;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

it('avoids sending notifications when members like their own posts', function (): void {
    /**
     * Fabricate a post whose author is the acting user.
     */
    Notification::fake();
    $user = User::factory()->create();
    $post = Post::query()->create([
        'user_id' => $user->id,
        'content' => 'Self-authored post should not trigger notifications.',
    ]);

    /**
     * Execute the Livewire action and observe component state changes.
     */
    actingAs($user);
    Livewire::test(LikeButton::class, ['postId' => $post->id])
        ->call('toggleLike')
        ->assertSet('isLiked', true)
        ->assertSet('likeCount', 1);

    /**
     * Confirm the like record exists while ensuring no notifications were dispatched.
     */
    expect(Like::query()->where('post_id', $post->id)->count())->toBe(1);
    Notification::assertNothingSent();
});
