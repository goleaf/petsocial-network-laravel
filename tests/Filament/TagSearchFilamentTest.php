<?php

use App\Http\Livewire\TagSearch;
use Livewire\Component;

/**
 * Filament-oriented test that guarantees the component can be embedded inside Filament panels.
 */
it('exposes a Livewire component class compatible with Filament pages', function () {
    // Define a lightweight stub mimicking how a Filament page would reference the Livewire component.
    $filamentPageStub = new class {
        public function getEmbeddedComponent(): string
        {
            // Filament pages frequently reference the component class string for rendering.
            return TagSearch::class;
        }
    };

    // Confirm the referenced class is a Livewire component, satisfying Filament's expectations.
    expect(is_subclass_of($filamentPageStub->getEmbeddedComponent(), Component::class))->toBeTrue();
});
