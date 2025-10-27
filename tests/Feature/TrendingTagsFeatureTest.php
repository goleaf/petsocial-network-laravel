<?php

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

/**
 * Feature tests covering the trending tags widget rendered inside the dashboard layout.
 */
it('shows the highest ranked tags on the dashboard sidebar', function () {
    // Create a verified user so the dashboard route and layout can be rendered.
    $viewer = User::factory()->create();

    // Seed a handful of posts with tag relationships so the component has data to aggregate.
    $popularTag = Tag::create(['name' => 'Playdates']);
    $secondaryTag = Tag::create(['name' => 'Rescues']);

    $author = User::factory()->create();

    // Attach three posts to the most popular tag to guarantee ordering.
    collect(range(1, 3))->each(function () use ($popularTag, $author) {
        $post = Post::create(['user_id' => $author->id, 'content' => 'Park meetup']);
        $post->tags()->attach($popularTag->id);
    });

    // Attach a single post to the secondary tag for contrast in the count output.
    $post = Post::create(['user_id' => $author->id, 'content' => 'Adoption day recap']);
    $post->tags()->attach($secondaryTag->id);

    // Visit the dashboard while authenticated to confirm the sidebar renders trending tags.
    $response = $this->actingAs($viewer)->get(route('dashboard'));

    // Verify the component lists the hottest tag first with an accurate post count.
    $response->assertSeeText('#Playdates (3 Posts)');
    $response->assertSeeText('#Rescues (1 Posts)');
});
