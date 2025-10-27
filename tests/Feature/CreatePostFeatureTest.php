<?php

use App\Http\Livewire\Content\CreatePost;
use App\Models\ActivityLog;
use App\Models\Pet;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Support\PostDraft;

/**
 * Feature-level coverage for the CreatePost Livewire component lifecycle.
 */
it('creates a post with tags, clears drafts, and notifies mentioned users', function () {
    // Fake notifications so we can assert mention delivery without hitting external channels.
    Notification::fake();

    // Provision the author, a pet they own, and a friend who will be mentioned in the post.
    $author = User::factory()->create();
    $pet = Pet::factory()->create(['user_id' => $author->id]);
    $mentioned = User::factory()->create(['username' => 'buddy']);

    // Seed a draft so the component exercises its draft clearing behaviour once saved.
    PostDraft::create([
        'id' => 'draft-1',
        'user_id' => $author->id,
        'content' => 'Draft content to replace',
        'tags' => 'old, draft',
        'pet_id' => $pet->id,
        'visibility' => 'friends',
    ]);

    $this->actingAs($author);

    // Drive the Livewire component through a save call with mention and tag data.
    Livewire::test(CreatePost::class)
        ->set('content', 'Morning walk with @'.$mentioned->username)
        ->set('tags', 'Outdoors, Fitness')
        ->set('pet_id', $pet->id)
        ->set('visibility', 'friends')
        ->call('save')
        ->assertHasNoErrors();

    $post = $author->posts()->first();

    // Confirm the post persisted with the requested visibility and ownership metadata.
    expect($post)->not->toBeNull()
        ->and($post->content)->toBe('Morning walk with @'.$mentioned->username)
        ->and($post->pet_id)->toBe($pet->id);

    // Ensure tags were normalised and attached through the pivot table.
    expect($post->tags()->pluck('name')->all())->toMatchArray(['outdoors', 'fitness']);

    // Activity logs should capture the post creation event for auditing.
    expect(ActivityLog::where('action', 'post_created')->count())->toBe(1);

    // Draft records need to be cleared so subsequent visits start fresh.
    expect($author->postDrafts()->count())->toBe(0);

    // Mentioned users receive a database notification referencing the new post.
    Notification::assertSentTo(
        $mentioned,
        ActivityNotification::class,
        fn ($notification) => $notification->type === 'mention' && $notification->post->is($post)
            && $notification->fromUser->is($author)
    );
});
