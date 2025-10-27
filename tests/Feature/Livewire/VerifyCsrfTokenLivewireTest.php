<?php

use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

beforeEach(function () {
    // Register the probe component for all scenarios in this file.
    Livewire::component('csrf-probe', new class extends \Livewire\Component {
        /**
         * Holds the token captured when the component is mounted.
         */
        public string $token = '';

        /**
         * Flag indicating whether the most recent validation attempt matched the middleware token.
         */
        public bool $validated = false;

        /**
         * Cache the CSRF token emitted by the middleware as soon as the component boots.
         */
        public function mount(): void
        {
            // Capture the token present in the session to confirm Livewire shares middleware context.
            $this->token = csrf_token();
        }

        /**
         * Compare an incoming token to the active middleware token.
         */
        public function validateIncomingToken(string $token): void
        {
            // Hash comparison mirrors the behaviour Laravel's middleware performs internally.
            $this->validated = hash_equals(csrf_token(), $token);
        }

        /**
         * Render a stub view so the component can be tested without extra markup.
         */
        public function render(): string
        {
            // Minimal markup keeps the component lightweight for middleware validation checks.
            return <<<'blade'
                <div>csrf-probe</div>
            blade;
        }
    });
});

it('exposes the CSRF token to Livewire components', function () {
    // Generate a token inside the session so the component can read it via the middleware.
    Session::start();
    $token = Session::token();

    // The probe component should surface the same token when mounted.
    Livewire::test('csrf-probe')->assertSet('token', $token);
});

it('validates incoming tokens against the middleware-managed value', function () {
    // Start the session to align the stored token with the Livewire probe.
    Session::start();
    $token = Session::token();

    // A matching token should set the validation flag to true.
    Livewire::test('csrf-probe')
        ->call('validateIncomingToken', $token)
        ->assertSet('validated', true)
        // A mismatched token should flip the validation flag back to false.
        ->call('validateIncomingToken', 'invalid-token')
        ->assertSet('validated', false);
});
