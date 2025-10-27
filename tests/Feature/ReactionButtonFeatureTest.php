<?php

use App\Http\Livewire\Content\ReactionButton;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

/**
 * Feature coverage for the interactive reaction button component.
 */
it('loads reaction counts and highlights the viewers current selection', function () {
    // Create the post author and the viewer so we can assert personalised state handling.
    $author = User::factory()->create();
    $viewer = User::factory()->create();

    // Seed the post along with pre-existing reactions from both parties to simulate live data.
    $post = Post::create([
        'user_id' => $author->id,
        'content' => 'Our pets are ready for the weekend showcase!',
    ]);

    $otherFan = User::factory()->create();

    Reaction::create([
        'user_id' => $otherFan->id,
        'post_id' => $post->id,
        'type' => 'like',
    ]);

    Reaction::create([
        'user_id' => $viewer->id,
        'post_id' => $post->id,
        'type' => 'love',
    ]);

    // Authenticate as the viewer so the component can resolve their existing reaction.
    $this->actingAs($viewer);

    $component = Livewire::test(ReactionButton::class, ['postId' => $post->id]);

    // Confirm the component state reflects the viewer reaction and aggregated counts.
    $counts = $component->get('reactionCounts');

    expect($component->get('currentReaction'))->toBe('love')
        ->and($counts['love'] ?? 0)->toBe(1)
        ->and($counts['like'] ?? 0)->toBe(1);
});

it('notifies the post owner when a new reaction is recorded', function () {
    // Prepare a post owner and a reacting user to verify cross-account notifications.
    $author = User::factory()->create();
    $viewer = User::factory()->create();

    $post = Post::create([
        'user_id' => $author->id,
        'content' => 'Reaction notifications keep everyone in the loop.',
    ]);

    Notification::fake();

    // Authenticate the viewer so the component records the reaction against the session user.
    $this->actingAs($viewer);

    Livewire::test(ReactionButton::class, ['postId' => $post->id])
        ->call('react', 'wow');

    // Ensure the database now tracks the freshly created reaction.
    $this->assertDatabaseHas('reactions', [
        'user_id' => $viewer->id,
        'post_id' => $post->id,
        'type' => 'wow',
    ]);

    // Confirm the ActivityNotification was dispatched with the expected metadata.
    Notification::assertSentTo($author, ActivityNotification::class, function (ActivityNotification $notification) use ($post, $viewer) {
        return $notification->type === 'reaction'
            && $notification->post->is($post)
            && $notification->fromUser->is($viewer);
    });
});
