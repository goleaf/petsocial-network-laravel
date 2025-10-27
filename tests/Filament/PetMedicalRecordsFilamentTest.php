<?php

use App\Http\Livewire\Pet\MedicalRecords;
use App\Models\Pet;

/**
 * Filament centric smoke test verifying the component remains embeddable.
 */
test('medical records component exposes a renderable view for filament panels', function () {
    // Skip gracefully when Filament packages are not present in the project.
    if (! class_exists('Filament\\Panel')) {
        test()->markTestSkipped('Filament is not installed for this application.');
    }

    // Resolve the Livewire component so Filament pages could embed it as a widget.
    $component = app(MedicalRecords::class);
    $component->pet = Pet::factory()->make([
        'id' => 1,
        'user_id' => 1,
        'name' => 'Shadow',
    ]);
    $component->record = null;

    $view = $component->render();

    // Filament expects a Blade view reference when mounting Livewire components.
    expect($view->name())->toBe('livewire.pet.medical-records');
});
