<?php

use App\Http\Livewire\TagSearch;
use Livewire\WithPagination;

/**
 * Unit coverage for TagSearch component defaults and traits.
 */
it('defaults the search term to an empty string', function () {
    // Instantiate the component directly to inspect its initial public state.
    $component = new TagSearch();

    // Confirm the default search term is empty so blank pages show initial results.
    expect($component->search)->toBe('');
});

it('uses the pagination trait required for long-running tag queries', function () {
    // Gather the recursive trait usage list to confirm pagination support is enabled.
    $traits = class_uses_recursive(TagSearch::class);

    // The WithPagination trait ensures Filament and Livewire contexts share consistent paging behaviour.
    expect($traits)->toHaveKey(WithPagination::class);
});
