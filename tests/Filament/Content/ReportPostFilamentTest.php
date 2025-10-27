<?php

use App\Http\Livewire\Content\ReportPost;

// Verify the component can integrate with a Filament action when the package is available.
test('report post component can back a filament table action', function (): void {
    // Skip the test gracefully when Filament is not installed in this application.
    if (! class_exists('Filament\\Tables\\Actions\\Action')) {
        $this->markTestSkipped('Filament is not installed for this project.');
    }

    /** @var class-string $actionClass */
    $actionClass = 'Filament\\Tables\\Actions\\Action';

    // Build a Filament action that renders the Livewire component for moderation workflows.
    $action = $actionClass::make('reportPost')->livewire(ReportPost::class);

    expect($action->getLivewireComponent())->toBe(ReportPost::class);
});
