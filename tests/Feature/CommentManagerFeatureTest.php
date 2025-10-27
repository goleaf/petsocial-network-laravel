<?php

use App\Http\Livewire\Common\CommentManager;
use App\Models\ActivityLog;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    // Allow mass-assignment on the Comment model so Livewire can persist payloads during tests.
    Comment::unguard();

    // Provide a no-op emit() macro so legacy event dispatches in the component do not error under Livewire v3.
    if (! Component::hasMacro('emit')) {
        Component::macro('emit', function (): Component {
            return $this;
        });
    }
});

afterEach(function (): void {
    // Restore mass-assignment protection to avoid leaking the relaxed state into other suites.
    Comment::reguard();
});

it('saves a comment, records an activity log, and notifies relevant users', function (): void {
    // Fake notifications to inspect which notifiable entities receive comment updates.
    Notification::fake();

    // Create the post owner, the authenticated commenter, and a mentioned friend with slug-friendly names for regex parsing.
    $postOwner = User::factory()->create(['name' => 'owner']);
    $commentAuthor = User::factory()->create(['name' => 'commenter']);
    $mentionedFriend = User::factory()->create(['name' => 'buddy']);

    // Persist a post that belongs to the owner so the component can resolve the relationship in mount().
    $post = Post::create([
        'user_id' => $postOwner->id,
        'content' => 'Original post body',
    ]);

    // Seed cache entries that should be invalidated after the comment is stored.
    Cache::put("post_{$post->id}_comments", collect());
    Cache::put("post_{$post->id}_comments_count", 99);
    Cache::put("post_{$post->id}_top_comments", collect());

    // Authenticate as the commenter to satisfy the component's auth() calls while submitting content with a mention.
    actingAs($commentAuthor);

    // Hydrate the Livewire component, fill in the body, and trigger the save action.
    Livewire::test(CommentManager::class, ['postId' => $post->id])
        ->set('content', 'First thought for @buddy to read')
        ->call('save')
        ->assertSet('content', '')
        ->assertSet('replyingToId', null);

    // Confirm the comment record exists with the authored content.
    expect(Comment::query()->count())->toBe(1)
        ->and(Comment::first()->content)->toBe('First thought for @buddy to read');

    // Ensure the owner and mentioned friend both received the expected activity notifications.
    Notification::assertSentToTimes($postOwner, ActivityNotification::class, 1);
    Notification::assertSentToTimes($mentionedFriend, ActivityNotification::class, 1);

    // Confirm an activity log entry captured the creation event metadata.
    expect(ActivityLog::query()->count())->toBe(1)
        ->and(ActivityLog::first()->description)->toContain('Commented on post ID '.$post->id);

});
