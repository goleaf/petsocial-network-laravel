<?php

use App\Http\Livewire\TagSearch;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

/**
 * Livewire-focused assertions for TagSearch behaviour.
 */
it('returns the newest matching posts with eager-loaded relations', function () {
    // Reset friendship caches to avoid stale results bleeding between component renders.
    Cache::flush();

    // Create the authenticated viewer and sign them in for component rendering.
    $viewer = User::factory()->create();
    $this->actingAs($viewer);

    // Prepare two tags so that only one post matches the search phrase.
    $sunTag = Tag::create(['name' => 'sunny']);
    $moonTag = Tag::create(['name' => 'moonlight']);

    // Matching post tagged with "sunny" should surface in the filtered collection.
    $matchingPost = new Post(['content' => 'A sunny walk', 'user_id' => $viewer->id]);
    $matchingPost->posts_visibility = 'public';
    $matchingPost->save();
    $matchingPost->tags()->attach($sunTag->id);

    // Non-matching post ensures filter logic excludes unrelated tags.
    $nonMatchingPost = new Post(['content' => 'Moonlit stroll', 'user_id' => $viewer->id]);
    $nonMatchingPost->posts_visibility = 'public';
    $nonMatchingPost->save();
    $nonMatchingPost->tags()->attach($moonTag->id);

    // Execute the Livewire component and verify the dataset respects the filter and ordering.
    Livewire::test(TagSearch::class)
        ->set('search', 'sun')
        ->call('render')
        // Confirm the component continues to reference the dedicated Blade view file.
        ->assertViewIs('livewire.tag-search')
        ->assertViewHas('posts', function ($posts) use ($matchingPost, $nonMatchingPost) {
            $collection = collect($posts->items());

            expect($collection->pluck('id')->all())
                ->toContain($matchingPost->id)
                ->not->toContain($nonMatchingPost->id);

            // Confirm eager loading includes the tags relationship for downstream UI components.
            expect($collection->first()->relationLoaded('tags'))->toBeTrue();

            return true;
        });
});
