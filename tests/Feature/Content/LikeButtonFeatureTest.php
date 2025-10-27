<?php

use App\Http\Livewire\Content\LikeButton;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

it('allows members to toggle likes and notifies post owners', function (): void {
    /**
     * Prepare a post owner, a liking member, and the post under test.
     */
    Notification::fake();
    $postOwner = User::factory()->create();
    $likingMember = User::factory()->create();
    $post = Post::query()->create([
        'user_id' => $postOwner->id,
        'content' => 'Likeable content that should trigger notifications.',
    ]);

    /**
     * Authenticate as the liking member so the Livewire component has context.
     */
    actingAs($likingMember);

    /**
     * Trigger the like action and confirm the component reflects the new state.
     */
    $component = Livewire::test(LikeButton::class, ['postId' => $post->id]);
    $component->call('toggleLike')
        ->assertSet('isLiked', true)
        ->assertSet('likeCount', 1);

    /**
     * Validate that the like persisted and the post owner received a notification.
     */
    expect(Like::query()->where('user_id', $likingMember->id)->where('post_id', $post->id)->exists())->toBeTrue();
    Notification::assertSentToTimes($postOwner, ActivityNotification::class, 1);

    /**
     * Trigger the unlike flow and ensure the record is removed for cleanup.
     */
    $component->call('toggleLike')
        ->assertSet('isLiked', false)
        ->assertSet('likeCount', 0);
    expect(Like::query()->count())->toBe(0);
});
