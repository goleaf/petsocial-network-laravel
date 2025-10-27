<?php

use App\Http\Livewire\UserDashboard;
use Livewire\Component;

/**
 * Filament-oriented verification ensuring the dashboard component remains registrable.
 */
it('registers cleanly with a simulated Filament widget registry', function () {
    // Create a lightweight registry that mirrors Filament\Support\Panel registration semantics.
    $registry = new class () {
        /** @var array<string, string> */
        public array $components = [];

        /**
         * Store the provided alias and component pairing for later assertions.
         */
        public function register(string $alias, string $componentClass): void
        {
            $this->components[$alias] = $componentClass;
        }
    };

    // Use the conventional alias format Filament adopts when binding Livewire widgets.
    $alias = 'app::user-dashboard';

    // Register the dashboard component so the fake registry mimics Filament bootstrapping.
    $registry->register($alias, UserDashboard::class);

    // Confirm the registry captured the alias and that the component remains Livewire-compatible for Filament.
    expect($registry->components[$alias])->toBe(UserDashboard::class)
        ->and(is_subclass_of(UserDashboard::class, Component::class))->toBeTrue();
});
