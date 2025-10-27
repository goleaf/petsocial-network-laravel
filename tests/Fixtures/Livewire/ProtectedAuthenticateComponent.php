<?php

namespace Tests\Fixtures\Livewire;

use Livewire\Component;

/**
 * Livewire component fixture that requires authentication for middleware testing scenarios.
 */
class ProtectedAuthenticateComponent extends Component
{
    /**
     * Ensure the Authenticate middleware handles the component's interactions.
     */
    protected $middleware = ['auth'];

    public function render(): string
    {
        // Render a placeholder template because only middleware responses are evaluated in tests.
        return <<<'BLADE'
            <div>secure</div>
        BLADE;
    }
}
