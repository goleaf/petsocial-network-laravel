<?php

use App\Http\Livewire\TrendingTags;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

use function Pest\Laravel\actingAs;

/**
 * Feature coverage validating the TrendingTags component renders inside Blade layouts.
 */
it('exposes trending tags within the primary application layout', function (): void {
    // Authenticate a member so the dashboard route and Livewire dependencies resolve correctly.
    $viewer = User::factory()->create();
    actingAs($viewer);

    // Seed two tags with different engagement levels to simulate the trending calculation.
    $leadingTag = Tag::create(['name' => 'CalmCats']);
    $secondaryTag = Tag::create(['name' => 'PlayfulPups']);

    // Attach two posts to the leading tag so it outranks the secondary option.
    collect(['Cat tower adventures', 'Sunbeam naps'])->each(function (string $content) use ($viewer, $leadingTag): void {
        // Each post boosts the relationship count that the component orders against.
        $post = Post::create(['user_id' => $viewer->id, 'content' => $content]);
        $post->tags()->attach($leadingTag->id);
    });

    // Create a single post for the secondary tag to validate ordering in the rendered view.
    $supportingPost = Post::create(['user_id' => $viewer->id, 'content' => 'Playground meetup highlights']);
    $supportingPost->tags()->attach($secondaryTag->id);

    // Disable Vite to prevent asset compilation from interfering with the HTTP response assertions.
    $this->withoutVite();

    // Render the dashboard which utilises the shared application layout embedding the Livewire widget.
    $response = $this->get(route('dashboard'));

    // Confirm the page renders successfully and still includes the Livewire component hook.
    $response->assertOk();
    $response->assertSeeLivewire(TrendingTags::class);

    // Verify the trending tag names surface in popularity order alongside the post counts.
    $response->assertSeeInOrder([
        '#CalmCats (2 Posts)',
        '#PlayfulPups (1 Posts)',
    ]);
});

