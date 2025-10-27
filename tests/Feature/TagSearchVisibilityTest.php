<?php

use App\Http\Livewire\TagSearch;
use App\Models\Friendship;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

/**
 * Feature tests that exercise the TagSearch component against real database records.
 */
it('filters posts by tag while respecting friendships and blocks', function () {
    // Ensure cached friendship lookups start from a clean slate for this scenario.
    Cache::flush();

    // Create the authenticated viewer alongside a friend, a blocked user, and a stranger.
    $viewer = User::factory()->create();
    $friend = User::factory()->create();
    $blocked = User::factory()->create();
    $stranger = User::factory()->create();

    // Persist an accepted friendship so the viewer can access friends-only content.
    Friendship::create([
        'sender_id' => $viewer->id,
        'recipient_id' => $friend->id,
        'status' => Friendship::STATUS_ACCEPTED,
        'accepted_at' => now(),
    ]);

    // Store the block relationship to confirm blocked authors are filtered out.
    $viewer->blocks()->attach($blocked->id);

    // Prepare a shared tag that ties the posts together for filtering.
    $tag = Tag::create(['name' => 'adventure']);

    // Friend post should surface because the viewer is part of the accepted friendship.
    $friendPost = new Post(['content' => 'Friend adventure story', 'user_id' => $friend->id]);
    $friendPost->posts_visibility = 'friends';
    $friendPost->save();
    $friendPost->tags()->attach($tag->id);

    // The viewer's own post should always appear regardless of visibility setting.
    $ownPost = new Post(['content' => 'My adventure recap', 'user_id' => $viewer->id]);
    $ownPost->posts_visibility = 'friends';
    $ownPost->save();
    $ownPost->tags()->attach($tag->id);

    // Blocked author's post must never surface despite being public.
    $blockedPost = new Post(['content' => 'Blocked adventure', 'user_id' => $blocked->id]);
    $blockedPost->posts_visibility = 'public';
    $blockedPost->save();
    $blockedPost->tags()->attach($tag->id);

    // Stranger's friends-only post should remain hidden because there is no friendship.
    $strangerPost = new Post(['content' => 'Secret adventure', 'user_id' => $stranger->id]);
    $strangerPost->posts_visibility = 'friends';
    $strangerPost->save();
    $strangerPost->tags()->attach($tag->id);

    // Authenticate as the viewer before interacting with the Livewire component.
    $this->actingAs($viewer);

    // Execute the component search and capture the rendered dataset for inspection.
    $component = Livewire::test(TagSearch::class)
        ->set('search', 'advent')
        ->call('render');

    // Inspect the paginator payload to ensure only the allowed posts are present.
    $component->assertViewHas('posts', function ($posts) use ($friendPost, $ownPost, $blockedPost, $strangerPost) {
        $ids = collect($posts->items())->pluck('id')->all();

        expect($ids)->toContain($friendPost->id)
            ->and($ids)->toContain($ownPost->id)
            ->and($ids)->not->toContain($blockedPost->id)
            ->and($ids)->not->toContain($strangerPost->id);

        return true;
    });
});
