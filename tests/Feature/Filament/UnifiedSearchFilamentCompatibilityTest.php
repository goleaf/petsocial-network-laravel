<?php

use App\Http\Livewire\Common\UnifiedSearch;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\actingAs;

/**
 * Ensures unified search datasets can hydrate Filament table builders and global search feeds.
 */
it('exposes structured result data compatible with Filament table hydration', function () {
    // Flush cache so freshly created content flows into the aggregation query.
    Cache::flush();

    // Authenticate a member who will execute the discovery query.
    $member = User::factory()->create([
        'profile_visibility' => 'public',
        'posts_visibility' => 'public',
        'location' => 'Austin',
    ]);
    actingAs($member);

    // Seed a tagged post so the search results provide a meaningful dataset for Filament tables.
    $post = new Post([
        'content' => 'Austin park adventure',
    ]);
    $post->user()->associate($member);
    $post->posts_visibility = 'public';
    $post->save();

    $tag = Tag::query()->create(['name' => 'adventures']);
    $post->tags()->attach($tag->id);

    // Use a lightweight harness to exercise the protected search aggregation helper.
    $component = new class extends UnifiedSearch {
        /**
         * Helper for tests to access the protected getSearchResults method.
         */
        public function exposeResults(): array
        {
            return $this->getSearchResults();
        }
    };

    // Mount the component then prime the search state similar to the Filament global search overlay.
    $component->mount('', 'all');
    $component->query = 'adventure';
    $component->perPage = 5;

    $results = $component->exposeResults();

    // Validate the aggregated payload structure so Filament table transformers can rely on it.
    expect($results)->toHaveKeys(['posts', 'users', 'pets', 'tags', 'events', 'total']);
    expect($results['posts']->total())->toBeGreaterThan(0);

    // Shape a simplified table row similar to how Filament tables expect label and description fields.
    $tableRows = collect($results['posts']->items())->map(fn ($record) => [
        'title' => $record->content,
        'owner' => optional($record->user)->name,
    ]);

    expect($tableRows->first())->toMatchArray([
        'title' => 'Austin park adventure',
        'owner' => $member->name,
    ]);
});
