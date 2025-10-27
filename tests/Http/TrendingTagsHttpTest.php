<?php

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

/**
 * HTTP-level verification to ensure the trending tag links route visitors correctly.
 */
it('links each trending tag to the authenticated tag search endpoint', function () {
    // Create a signed-in viewer to satisfy the authentication middleware on both routes.
    $viewer = User::factory()->create();

    // Populate a trending tag with enough engagement to surface in the sidebar list.
    $tag = Tag::create(['name' => 'Nutrition']);
    $author = User::factory()->create();

    // Persist a single tagged post so the component renders an actionable link.
    $post = Post::create(['user_id' => $author->id, 'content' => 'Healthy treats']);
    $post->tags()->attach($tag->id);

    // Capture the dashboard markup to ensure the anchor points at the expected destination.
    $dashboard = $this->actingAs($viewer)->get(route('dashboard'));
    $dashboard->assertOk();
    $dashboard->assertSee('href="' . route('tag.search') . '?search=Nutrition"', false);
});
