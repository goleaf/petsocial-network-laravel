<?php

use App\Http\Livewire\Group\Management\Index;

it('resets the management form back to its default values', function (): void {
    // Instantiate the component directly to exercise the simple state mutation helper.
    /** @var Index $component */
    $component = app(Index::class);

    // Populate fields to confirm the reset helper clears every property consistently.
    $component->name = 'Evening Walkers';
    $component->description = 'Coordinate relaxed sunset strolls.';
    $component->categoryId = 42;
    $component->visibility = 'secret';
    $component->groupRules = ['Keep locations confidential'];
    $component->location = 'Austin';
    $component->coverImage = 'path/to/cover.jpg';
    $component->icon = 'path/to/icon.png';

    // Invoke the helper and ensure each tracked property is restored to its baseline state.
    $component->resetForm();

    expect($component->name)->toBe('')
        ->and($component->description)->toBe('')
        ->and($component->categoryId)->toBe('')
        ->and($component->visibility)->toBe('open')
        ->and($component->groupRules)->toBe([])
        ->and($component->location)->toBe('')
        ->and($component->coverImage)->toBeNull()
        ->and($component->icon)->toBeNull();
});
