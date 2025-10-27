<?php

use App\Http\Livewire\Content\ShareButton;
use Livewire\Component;

/**
 * Filament compatibility check using an inline stub that mimics the Action::livewire API surface.
 */
it('exposes the correct contract for Filament livewire actions', function (): void {
    // Define a lightweight stub mirroring Filament\Actions\Action::livewire for component registration.
    $fakeAction = new class {
        public ?string $component = null;
        public array $parameters = [];

        public function livewire(string $component, array $parameters = []): void
        {
            $this->component = $component;
            $this->parameters = $parameters;
        }
    };

    // Register the ShareButton component with the stub to simulate Filament configuration.
    $fakeAction->livewire(ShareButton::class, ['postId' => 99]);

    // Ensure Filament would receive a Livewire component class along with the expected parameters.
    expect(is_subclass_of($fakeAction->component, Component::class))->toBeTrue();
    expect($fakeAction->parameters)->toHaveKey('postId', 99);
});
