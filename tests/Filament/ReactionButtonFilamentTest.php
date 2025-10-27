<?php

use App\Http\Livewire\Content\ReactionButton;

/**
 * Filament-adjacent expectations ensure the component feeds admin widgets clean data.
 */
it('produces reaction options suitable for Filament select components', function () {
    // Instantiate the component so we can transform its configuration for Filament usage.
    $component = new ReactionButton();

    // Mimic the structure Filament\Forms\Components\Select::options would expect.
    $options = collect($component->reactionTypes)
        ->mapWithKeys(fn ($emoji, $type) => [$type => sprintf('%s %s', strtoupper($type), $emoji)]);

    // Validate the generated labels and keys are well-formed for Filament integration.
    foreach ($options as $type => $label) {
        expect($type)->toBeString()
            ->and($label)->toBeString()
            ->and(str_contains($label, $component->reactionTypes[$type]))->toBeTrue();
    }
});
