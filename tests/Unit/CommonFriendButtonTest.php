<?php

use App\Http\Livewire\Common\Friend\Button;

it('toggles the dropdown visibility flag locally', function () {
    // Instantiate the component directly to validate the lightweight toggle logic.
    $component = app(Button::class);

    // Ensure the dropdown becomes visible after the first toggle call.
    $component->showDropdown = false;
    $component->toggleDropdown();
    expect($component->showDropdown)->toBeTrue();

    // A subsequent toggle should collapse the dropdown again for proper UX behaviour.
    $component->toggleDropdown();
    expect($component->showDropdown)->toBeFalse();
});
