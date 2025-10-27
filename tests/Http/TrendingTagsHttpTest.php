<?php

use App\Http\Livewire\TrendingTags;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * HTTP layer validation ensuring the TrendingTags component can be routed directly.
 */
it('serves the trending tag widget via an ad-hoc HTTP endpoint', function (): void {
    // Authenticate a user so Livewire can resolve authenticated dependencies while rendering.
    $member = User::factory()->create();
    $this->actingAs($member);

    // Create sample tags and attach posts to mirror a realistic trending distribution.
    $trailDogs = Tag::create(['name' => 'TrailDogs']);
    $sunsetCats = Tag::create(['name' => 'SunsetCats']);

    // Record engagement for each tag to populate the posts_count relationship.
    $leadPost = Post::create(['user_id' => $member->id, 'content' => 'Trail adventures recap']);
    $leadPost->tags()->attach($trailDogs->id);
    $bonusPost = Post::create(['user_id' => $member->id, 'content' => 'Trail snack checklist']);
    $bonusPost->tags()->attach($trailDogs->id);
    $supportingPost = Post::create(['user_id' => $member->id, 'content' => 'Sunset lounging tips']);
    $supportingPost->tags()->attach($sunsetCats->id);

    // Register a lightweight web route that points directly to the Livewire component under test.
    Route::middleware('web')->get('/testing/trending-tags', TrendingTags::class);

    // Disable Vite asset compilation because the response assertions only rely on HTML output.
    $this->withoutVite();

    // Dispatch the HTTP request and capture the component-driven response for validation.
    $response = $this->get('/testing/trending-tags');

    // Confirm the component renders successfully and exposes the expected tag metadata.
    $response->assertOk();
    $response->assertSee('#TrailDogs (2 Posts)');
    $response->assertSee('#SunsetCats (1 Posts)');
});

