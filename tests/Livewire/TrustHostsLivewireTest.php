<?php

namespace Tests\Livewire;

use App\Http\Middleware\TrustHosts;
use Livewire\Component;

// Lightweight Livewire component dedicated to surfacing the middleware output for verification.
final class TrustHostsPreviewComponent extends Component
{
    public string $pattern = '';

    public function mount(): void
    {
        // Resolve the middleware and capture the first trusted host pattern for assertions.
        $middleware = new TrustHosts(app());
        $hosts = $middleware->hosts();
        $this->pattern = $hosts[0] ?? '';
    }

    public function render(): string
    {
        // Render the pattern so the test can verify it is exposed to the frontend layer.
        return <<<'blade'
            <div>{{ $pattern }}</div>
        blade;
    }
}

namespace Tests;

use Livewire\Livewire;
use Tests\Livewire\TrustHostsPreviewComponent;

it('surfaces the trusted host regex inside a Livewire component', function (): void {
    // Match the application URL used throughout the middleware test suite.
    config(['app.url' => 'https://petsocial.test']);

    Livewire::test(TrustHostsPreviewComponent::class)
        // Confirm the computed pattern is stored on the Livewire component state.
        ->assertSet('pattern', '^(.+\\.)?petsocial\\.test$')
        // Ensure the rendered HTML exposes the trusted host regex for downstream consumers.
        ->assertSee('^(.+\\.)?petsocial\\.test$');
});
