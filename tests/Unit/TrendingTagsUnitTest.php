<?php

use App\Http\Livewire\TrendingTags;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

/**
 * Unit tests validating the data-loading logic that powers the trending tag feed.
 */
it('collects the top ten tags ordered by post volume', function () {
    // Create an author to satisfy the foreign key on generated posts.
    $author = User::factory()->create();

    // Generate twelve tags with descending popularity to confirm the limiting behaviour.
    $tags = collect(range(1, 12))->map(function ($rank) {
        return Tag::create(['name' => "Tag{$rank}"]);
    });

    // Attach a matching number of posts to each tag so post counts decrease as the rank increases.
    $tags->each(function (Tag $tag, int $index) use ($author) {
        $postTotal = 13 - ($index + 1);

        collect(range(1, $postTotal))->each(function () use ($author, $tag) {
            $post = Post::create(['user_id' => $author->id, 'content' => 'Trending insight']);
            $post->tags()->attach($tag->id);
        });
    });

    // Execute the loader directly to ensure the component calculates the expected collection.
    $component = new TrendingTags();
    $component->loadTrendingTags();

    // Guarantee the Blade view used by the Livewire component remains available to avoid runtime errors.
    expect(view()->exists('livewire.trending-tags'))->toBeTrue();

    // Validate that only ten tags are kept and they remain sorted by popularity.
    expect($component->trendingTags)->toHaveCount(10)
        ->and($component->trendingTags->first()->name)->toBe('Tag1')
        ->and($component->trendingTags->last()->name)->toBe('Tag10');
});
