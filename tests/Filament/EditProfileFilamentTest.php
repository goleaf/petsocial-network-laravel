<?php

use App\Http\Livewire\EditProfile;
use Livewire\Component;

/**
 * Filament compatibility tests ensuring the Livewire component can be embedded inside admin panels.
 */
it('remains embeddable as a Filament-compatible Livewire component', function () {
    // Confirm the component extends Livewire\Component so Filament panels can mount it as a widget or form section.
    expect(is_subclass_of(EditProfile::class, Component::class))->toBeTrue();

    // Verify the render method exists so Filament can resolve the Blade view when registering the component.
    expect(method_exists(EditProfile::class, 'render'))->toBeTrue();
});
