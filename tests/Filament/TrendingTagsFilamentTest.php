<?php

use App\Http\Livewire\TrendingTags;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

/**
 * Filament integration smoke tests confirming the component exposes data in admin-friendly formats.
 */
it('maps trending tags into label/value pairs suitable for Filament selects', function () {
    // Create a content author that will be associated with each seeded post.
    $author = User::factory()->create();

    // Populate two tags with distinct engagement levels to simulate admin analytics data.
    $spotlight = Tag::create(['name' => 'Wellness']);
    $supporting = Tag::create(['name' => 'Training']);

    // Record three posts for the spotlight tag so it is prioritised in the output ordering.
    collect(range(1, 3))->each(function () use ($author, $spotlight) {
        $post = Post::create(['user_id' => $author->id, 'content' => 'Care tips']);
        $post->tags()->attach($spotlight->id);
    });

    // Record a single supporting post for the secondary tag to provide additional options.
    $post = Post::create(['user_id' => $author->id, 'content' => 'Obedience drills']);
    $post->tags()->attach($supporting->id);

    // Load the component data so the Filament-oriented transformation can be evaluated.
    $component = new TrendingTags();
    $component->loadTrendingTags();

    // Convert the trending collection into the label/value structure Filament select components expect.
    $options = $component->trendingTags->map(function ($tag) {
        return [
            'label' => '#' . $tag->name . ' (' . $tag->posts_count . ' Posts)',
            'value' => $tag->name,
        ];
    });

    // Ensure the transformed collection prioritises the busiest tag and retains accurate counts.
    expect($options->first())->toMatchArray([
        'label' => '#Wellness (3 Posts)',
        'value' => 'Wellness',
    ])->and($options->last())->toMatchArray([
        'label' => '#Training (1 Posts)',
        'value' => 'Training',
    ]);
});
