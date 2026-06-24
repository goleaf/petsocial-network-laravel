<?php

use App\Http\Livewire\TrendingTags;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Livewire\Livewire;

/**
 * Livewire-specific tests verifying the component lifecycle and rendering pipeline.
 */
it('refreshes the trending collection after new activity is recorded', function () {
    // Create the author responsible for the posts that will drive tag popularity.
    $author = User::factory()->create();

    // Prepare two tags so we can update popularity between renders.
    $hotTag = Tag::create(['name' => 'Puppies']);
    $coolingTag = Tag::create(['name' => 'Kittens']);

    // Seed the initial popularity snapshot prior to invoking the Livewire component.
    $initialPost = Post::create(['user_id' => $author->id, 'content' => 'Morning zoomies']);
    $initialPost->tags()->attach($hotTag->id);

    // Hydrate the component and capture the baseline ordering.
    $component = Livewire::test(TrendingTags::class);
    // Confirm the component is wired to the expected Blade view so layout integrations stay stable.
    $component->assertViewIs('livewire.trending-tags');
    $component->assertViewHas('trendingTags', function ($tags) {
        return $tags->first()->name === 'Puppies';
    });

    // Record fresh engagement that should cause the other tag to climb the ranks.
    $surgePost = Post::create(['user_id' => $author->id, 'content' => 'Catnap stories']);
    $surgePost->tags()->attach($coolingTag->id);
    $surgePostTwo = Post::create(['user_id' => $author->id, 'content' => 'Cat tree adventures']);
    $surgePostTwo->tags()->attach($coolingTag->id);

    // Trigger a manual refresh to reload the latest counts from the database.
    $component->call('loadTrendingTags')
        ->assertViewHas('trendingTags', function ($tags) {
            return $tags->first()->name === 'Kittens';
        });
});
