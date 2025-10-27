<?php

use App\Http\Livewire\Group\Topics\Index;

it('restores the default poll option structure when reset is requested', function () {
    // Instantiate the Livewire component directly to validate its internal helpers.
    $component = new Index();

    // Mutate the poll options to mimic a user editing the form.
    $component->pollOptions = [
        ['text' => 'First choice'],
        ['text' => 'Second choice'],
        ['text' => 'Third choice'],
    ];

    // Trigger the reset helper and confirm it rebuilds the canonical two-row structure.
    $component->resetPollOptions();

    expect($component->pollOptions)->toHaveCount(2)
        ->and($component->pollOptions[0]['text'])->toBe('')
        ->and($component->pollOptions[1]['text'])->toBe('');
});

it('toggles topic identifiers inside the bulk selection tracker', function () {
    // Directly target the selection helper so UI integrations stay reliable.
    $component = new Index();

    // Add a topic identifier, then remove it to ensure both transitions are handled.
    $component->toggleBulkSelect(42);
    expect($component->selectedTopics)->toContain(42);

    $component->toggleBulkSelect(42);
    expect($component->selectedTopics)->toBeEmpty();
});
