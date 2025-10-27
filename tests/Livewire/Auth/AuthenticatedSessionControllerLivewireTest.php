<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\Livewire\Support\DisplaysAuthState;

use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\post;

// Livewire-focused assertions confirming the controller keeps component state in sync.
describe('Authenticated session Livewire integration', function (): void {
    it('shares the authenticated state with Livewire components after login', function (): void {
        // Create a user account that can sign in through the controller.
        $user = User::factory()->create([
            'email' => 'livewire@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        // Before logging in, components should report the guest state.
        Livewire::test(DisplaysAuthState::class)->assertSee('guest');

        // Log in using the HTTP controller endpoint so session data is established.
        $response = post(route('login'), [
            'email' => 'livewire@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('dashboard'));
        assertAuthenticatedAs($user->fresh());

        // After authentication, the component should reflect the signed-in state.
        Livewire::test(DisplaysAuthState::class)->assertSee('authenticated');
    });

    it('returns Livewire components to the guest state after logout', function (): void {
        // Sign in an account so the logout workflow has a valid session to clear.
        $user = User::factory()->create([
            'email' => 'livewire-logout@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        post(route('login'), [
            'email' => 'livewire-logout@example.com',
            'password' => 'Password123!',
        ]);

        assertAuthenticatedAs($user->fresh());

        // Trigger the logout endpoint handled by the controller.
        $response = post(route('logout'));

        $response->assertRedirect('/');
        assertGuest();

        // Confirm that Livewire components once again render the guest marker.
        Livewire::test(DisplaysAuthState::class)->assertSee('guest');
    });
});
