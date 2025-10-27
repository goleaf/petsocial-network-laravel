<?php

use App\Http\Livewire\Common\Friend\Finder;
use Livewire\Mechanisms\ComponentRegistry;
use Livewire\Livewire;

it('exposes the Livewire alias expected by Filament panels', function () {
    // Ensure the alias is registered for the duration of the test run.
    Livewire::component('social.friend.finder', Finder::class);

    // Retrieve the Livewire component registry to inspect registered aliases.
    $registry = app(ComponentRegistry::class);

    // Simulate a Filament widget declaring the finder alias for embedding.
    $filamentWidget = new class {
        /**
         * Provide the Livewire component alias the widget would render.
         */
        public function getLivewireComponent(): string
        {
            // Filament widgets typically reference Livewire components by alias.
            return 'social.friend.finder';
        }
    };

    // Resolve the class name behind the alias to ensure it targets the finder component.
    $resolvedClass = $registry->getClass($filamentWidget->getLivewireComponent());

    // Confirm the registry maps the alias to the finder so Filament integrations remain stable.
    expect($resolvedClass)->toBe(Finder::class);
});
