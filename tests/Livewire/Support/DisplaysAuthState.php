<?php

namespace Tests\Livewire\Support;

use Illuminate\View\View;
use Livewire\Component;

/**
 * Lightweight Livewire component used in tests to expose the auth guard state.
 */
class DisplaysAuthState extends Component
{
    /**
     * Render a tiny blade snippet showing whether a user is authenticated.
     */
    public function render(): View
    {
        // Register a view namespace that resolves to the test-only blade snippets.
        app('view')->addNamespace('tests-livewire', __DIR__.'/../views');

        // Provide the template with a boolean for the current authentication status.
        return app('view')->make('tests-livewire::auth-state', [
            'authenticated' => auth()->check(),
        ]);
    }
}
