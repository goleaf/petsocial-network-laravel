<?php

use App\Http\Livewire\Pet\PetManagement;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

/**
 * Filament integration safety net for the pet management component.
 */
it('is ready for embedding within a Filament page', function (): void {
    // Bail out early when Filament is not present so the suite documents the missing dependency gracefully.
    if (! class_exists(\Filament\Facades\Filament::class)) {
        test()->markTestSkipped('Filament is not installed in this project; skip the integration smoke test.');
    }

    // Authenticate a user because the component expects an active session during rendering.
    $user = User::factory()->create();
    actingAs($user);

    // Render the component the same way a Filament view would embed it and ensure key markup is returned.
    $html = Livewire::test(PetManagement::class)->html();
    expect($html)->toContain('Manage Your Pets');
});
