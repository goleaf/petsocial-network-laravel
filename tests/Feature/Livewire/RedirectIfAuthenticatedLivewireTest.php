<?php

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Livewire tests verifying RedirectIfAuthenticated enforcement on guest Livewire pages.
 */

// Confirm authenticated users are redirected when visiting a guest Livewire route.
it('redirects authenticated users away from a guest Livewire route', function (): void {
    // Register a temporary Livewire route protected by the guest middleware.
    Route::middleware(['web', 'guest'])->get('/guest/livewire-probe', GuestLoginProbeComponent::class);

    // Register the Livewire component alias to ensure Livewire resolves the probe component correctly.
    Livewire::component('guest-login-probe', GuestLoginProbeComponent::class);

    // Create and authenticate a user prior to visiting the guest page.
    $member = User::factory()->make(['id' => 3001]);
    $this->actingAs($member);

    // Request the Livewire-powered guest page while authenticated.
    $response = $this->get('/guest/livewire-probe');

    // The middleware should redirect the user back to the configured home route.
    $response->assertRedirect(RouteServiceProvider::HOME);
});

// Verify that unauthenticated guests can render the Livewire route successfully.
it('renders the guest Livewire route for unauthenticated visitors', function (): void {
    // Register the same Livewire guest route to evaluate guest rendering.
    Route::middleware(['web', 'guest'])->get('/guest/livewire-probe', GuestLoginProbeComponent::class);
    Livewire::component('guest-login-probe', GuestLoginProbeComponent::class);

    // Visit the guest Livewire page without authentication.
    $response = $this->get('/guest/livewire-probe');

    // Confirm the guest can see the Livewire view output.
    $response->assertOk();
    $response->assertSee('guest livewire login');
});

// Inline Livewire component stub to exercise the guest middleware without touching production components.
#[Layout('layouts.blank')]
final class GuestLoginProbeComponent extends Component
{
    /**
     * Render a minimal guest view to keep the probe lightweight.
     */
    public function render(): string
    {
        // The inline Blade template supplies recognizable copy for assertions.
        return <<<'blade'
            <div>guest livewire login</div>
        blade;
    }
}
