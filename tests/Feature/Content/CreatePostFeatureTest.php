<?php

use App\Http\Livewire\Content\CreatePost;
use App\Models\ActivityLog;
use App\Models\Post;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Support\FakePostDraftRelation;

it('creates a post, attaches tags, logs activity, and notifies mentions', function () {
    // Prepare the author and mentioned user to drive the Livewire workflow.
    $author = User::factory()->create(['username' => 'storyteller']);
    $mentioned = User::factory()->create(['username' => 'buddy']);

    // Authenticate as the author so the component executes with a valid guard context.
    $this->actingAs($author);

    // Fake notifications to inspect the mention broadcasting behaviour without side effects.
    Notification::fake();

    // Interact with the component to draft and publish a post containing tags and a mention.
    Livewire::test(CreatePost::class)
        ->set('content', 'Hanging with @buddy')
        ->set('tags', 'Outdoors, Sunshine')
        ->call('save')
        ->assertDispatched('postCreated');

    // Ensure the post persisted with the expected metadata and attached tags.
    $post = Post::first();
    expect($post)->not->toBeNull()
        ->and($post->user_id)->toBe($author->id)
        ->and($post->visibility)->toBe('public');

    // Confirm that both tags were created, normalized, and linked to the new post.
    $attachedTags = $post->tags()->pluck('name')->all();
    expect($attachedTags)->toMatchArray(['outdoors', 'sunshine']);

    // Validate that an activity log entry captured the creation event with the correct action.
    $logEntry = ActivityLog::first();
    expect($logEntry)->not->toBeNull()
        ->and($logEntry->action)->toBe('post_created');

    // Assert that the mentioned user received the structured activity notification.
    Notification::assertSentTo($mentioned, ActivityNotification::class, function (ActivityNotification $notification) use ($author, $post) {
        // Verify the payload references the author, mention type, and resulting post.
        return $notification->type === 'mention'
            && $notification->fromUser->is($author)
            && $notification->post->is($post);
    });

    // Double-check that the draft cache was cleared once the post was persisted.
    expect(FakePostDraftRelation::draftFor($author->id))->toBeNull();
});
