<?php

use App\Http\Livewire\Content\ShareButton;
use App\Models\Post;
use App\Models\Share;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

/**
 * High level feature test covering the share toggle workflow and notification dispatch.
 */
it('toggles share state and notifies the post author', function (): void {
    // Seed a post owner and an authenticated viewer to exercise the toggle behaviour.
    $author = User::factory()->create();
    $viewer = User::factory()->create();
    $post = Post::query()->create([
        'user_id' => $author->id,
        'content' => 'A playful pup update.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Fake notifications so we can assert on ActivityNotification payloads without sending real broadcasts.
    Notification::fake();

    // Authenticate the viewer to satisfy the component reliance on the global auth helper.
    actingAs($viewer);

    // Drive the Livewire component through its public share method to toggle the share on.
    $component = Livewire::test(ShareButton::class, ['postId' => $post->id]);
    $component->call('share')
        ->assertSet('isShared', true)
        ->assertSet('shareCount', 1);

    // Ensure the share record persisted for the authenticated viewer.
    expect(
        Share::query()->where('post_id', $post->id)->where('user_id', $viewer->id)->exists()
    )->toBeTrue();

    // Confirm the author receives a share notification with the correct context.
    Notification::assertSentTo($author, ActivityNotification::class, function (ActivityNotification $notification) use ($viewer, $post): bool {
        return $notification->type === 'share'
            && $notification->fromUser->is($viewer)
            && $notification->post->is($post);
    });

    // Trigger the share method again to cover the unshare branch and state resets.
    $component->call('share')
        ->assertSet('isShared', false)
        ->assertSet('shareCount', 0);

    // Validate the database reflects the unshared state for the viewer.
    expect(
        Share::query()->where('post_id', $post->id)->where('user_id', $viewer->id)->exists()
    )->toBeFalse();

    // The notification should only be dispatched on the initial share, not on unshare.
    Notification::assertSentToTimes($author, ActivityNotification::class, 1);
});

/**
 * Regression test ensuring authors are not notified when sharing their own content.
 */
it('avoids dispatching notifications when the author shares their own post', function (): void {
    // Use a single user as both the author and sharer to cover the self-share branch explicitly.
    $author = User::factory()->create();
    $post = Post::query()->create([
        'user_id' => $author->id,
        'content' => 'Solo training recap for agility course.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Prevent real notifications and authenticate the author for the component lifecycle.
    Notification::fake();
    actingAs($author);

    // Exercise the share toggle once to ensure the notification is suppressed.
    Livewire::test(ShareButton::class, ['postId' => $post->id])
        ->call('share')
        ->assertSet('isShared', true);

    // Assert the notification bus remained untouched for the self-share scenario.
    Notification::assertNothingSent();
});
