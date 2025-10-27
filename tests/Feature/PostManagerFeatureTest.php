<?php

use App\Http\Livewire\Common\PostManager;
use App\Models\ActivityLog;
use App\Models\Post;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('creates immediate posts with tags, mentions, and activity logging', function (): void {
    // Fake notifications so the mention workflow can be asserted without touching broadcast drivers.
    Notification::fake();

    // Create an author with permissive visibility along with a mention target that should receive notifications.
    $author = User::factory()->create([
        'name' => 'AuthorOne',
        'profile_visibility' => 'public',
        'privacy_settings' => User::PRIVACY_DEFAULTS,
    ]);

    $mentioned = User::factory()->create([
        'name' => 'Buddy',
        'profile_visibility' => 'public',
        'privacy_settings' => User::PRIVACY_DEFAULTS,
    ]);

    $this->actingAs($author);

    // Drive the Livewire component to create a post that publishes immediately.
    Livewire::test(PostManager::class, [
        'entityType' => 'user',
        'entityId' => $author->id,
    ])
        ->set('content', 'Hanging out with @Buddy later!')
        ->set('tags', 'Weekend, Pets ')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('postCreated');

    // Ensure the flash message that powers the UI toast was set appropriately.
    expect(session('message'))->toBe(__('posts.post_created'));

    // Confirm the post persisted with normalized tags and without scheduling metadata.
    $post = Post::with('tags')->first();

    expect($post)->not->toBeNull()
        ->and($post->content)->toBe('Hanging out with @Buddy later!')
        ->and($post->scheduled_for)->toBeNull()
        ->and($post->tags->pluck('name')->all())->toEqualCanonicalizing(['weekend', 'pets']);

    // Validate an activity log entry captured the creation event and preview metadata.
    $activity = ActivityLog::first();

    expect($activity)->not->toBeNull()
        ->and($activity->action)->toBe('post_created')
        ->and($activity->metadata['preview'])->toContain('Hanging out with @Buddy later');

    // Ensure the mentioned member received the appropriate notification payload.
    Notification::assertSentTo($mentioned, ActivityNotification::class, function (ActivityNotification $notification) use ($post) {
        // Confirm the notification references the correct post and activity type for the mention.
        return $notification->type === 'mention' && $notification->post->is($post);
    });
});
