<?php

use App\Http\Livewire\Group\Topics\Index;

/**
 * Unit tests exercise isolated helper logic within the group topics component.
 */
it('resets poll options to a predictable pair of blank entries', function (): void {
    // Instantiate the component directly so we can target the helper without Livewire runtime concerns.
    $component = app(Index::class);

    // Prime non-empty options to confirm the helper truly resets the collection.
    $component->pollOptions = [
        ['text' => 'Existing option'],
        ['text' => 'Another option'],
        ['text' => 'Third option'],
    ];

    $component->resetPollOptions();

    // The helper should leave exactly two blank option shells ready for user input.
    expect($component->pollOptions)->toBe([
        ['text' => ''],
        ['text' => ''],
    ]);
});

it('toggles topic identifiers inside the bulk selection list', function (): void {
    // Mount a fresh component instance that will maintain the selection state throughout the assertions.
    $component = app(Index::class);

    // Seed an initial selection so the toggle routine has meaningful data to operate on.
    $component->selectedTopics = [1, 2];

    $component->toggleBulkSelect(2);

    // Removing an id should prune it from the selection entirely.
    expect($component->selectedTopics)->toBe([1]);

    $component->toggleBulkSelect(3);

    // Adding a new id should append it without disturbing the existing entries.
    expect($component->selectedTopics)->toBe([1, 3]);
});
