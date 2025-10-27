<?php

namespace Tests\Livewire {

use Livewire\Component;

/**
 * Minimal Livewire component used to introspect the framework locale during tests.
 */
class FakeLocaleAwareComponent extends Component
{
    /**
     * Expose the resolved locale to Livewire assertions.
     */
    public string $activeLocale = '';

    public function mount(): void
    {
        // Capture the locale that the controller placed into the container/session.
        $this->activeLocale = app()->getLocale();
    }

    public function render(): string
    {
        // Return an inline Blade template so Livewire can complete the render cycle.
        return <<<'blade'
            <div>{{ $activeLocale }}</div>
        blade;
    }
}

}

namespace {

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;
use Tests\Livewire\FakeLocaleAwareComponent;

beforeEach(function (): void {
    // Reset the locale and purge the session before each Livewire assertion.
    App::setLocale(config('app.fallback_locale'));
    Session::flush();
});

it('propagates a supported locale change to Livewire components', function (): void {
    // Switch the application language through the controller endpoint.
    $this->from('/')->get(route('language.switch', ['locale' => 'ru']))->assertRedirect('/');

    // Register the helper component so Livewire::test can instantiate it.
    Livewire::component('fake-locale-aware', FakeLocaleAwareComponent::class);

    // Ensure the component receives the locale that the controller persisted.
    Livewire::test(FakeLocaleAwareComponent::class)
        ->assertSet('activeLocale', 'ru');
});

it('exposes the fallback locale when an invalid code is provided', function (): void {
    // Trigger the controller with an unsupported locale value to exercise the fallback branch.
    $this->from('/')->get(route('language.switch', ['locale' => 'jp']))->assertRedirect('/');

    // Register the helper component so the Livewire runtime can resolve it.
    Livewire::component('fake-locale-aware', FakeLocaleAwareComponent::class);

    // The component should surface the fallback locale, proving the controller guarded the input.
    Livewire::test(FakeLocaleAwareComponent::class)
        ->assertSet('activeLocale', config('app.fallback_locale'));
});

}
