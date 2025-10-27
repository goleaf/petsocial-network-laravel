<?php

use App\Http\Livewire\Common\UnifiedSearch;
use App\Models\SearchHistory;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

// Keep the in-memory database consistent between runs when exercising Livewire interactions.
uses(RefreshDatabase::class);

it('renders the unified search blade view for authenticated members', function (): void {
    // Flush stale cache fragments so pagination state does not leak between tests.
    Cache::flush();

    // Authenticate a member to satisfy the Livewire component guard clauses.
    $member = User::factory()->create();
    $this->actingAs($member);

    // Render the component and confirm the correct blade template is returned with the empty state copy.
    Livewire::test(UnifiedSearch::class)
        ->assertViewIs('livewire.common.unified-search')
        ->assertSee(__('search.enter_search_term'));
});

it('hydrates suggested content using recorded search history tags', function (): void {
    // Reset caches so the sidebar datasets are recomputed from scratch for this scenario.
    Cache::flush();

    // Seed a user along with a prior search history entry that references a hashtag.
    $member = User::factory()->create();
    $this->actingAs($member);

    $tag = Tag::query()->create(['name' => 'pugs']);

    SearchHistory::query()->create([
        'user_id' => $member->id,
        'query' => '#Pugs adventures',
        'search_type' => 'posts',
        'filters' => ['filter' => 'all'],
        'results_count' => 0,
    ]);

    // Trigger the component lifecycle to force suggestion recomputation.
    $component = Livewire::test(UnifiedSearch::class)
        ->set('query', 'weekend adventures')
        ->call('$refresh');

    // Extract the suggested content payload to assert the related tag surfaced for the member.
    $suggestions = $component->get('suggestedContent');

    expect($suggestions['tags']->pluck('name')->all())
        ->toContain($tag->name);
});
