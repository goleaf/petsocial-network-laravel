<?php

namespace Tests\Feature\Livewire\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Livewire coverage ensures the Volt registration page stays aligned with the controller behaviour.
 */
class RegisteredUserRegistrationLivewireTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function registers_a_user_via_the_livewire_registration_page(): void
    {
        // Arrange: Fake the Registered event to assert parity with the controller.
        Event::fake([Registered::class]);
        Notification::fake();

        // Act: Drive the Volt page component with valid credentials.
        Livewire::test('pages.auth.register')
            ->set('name', 'Livewire User')
            ->set('email', 'livewire@example.com')
            ->set('password', 'StrongPass123!')
            ->set('password_confirmation', 'StrongPass123!')
            ->call('register')
            ->assertRedirect(RouteServiceProvider::HOME);

        // Assert: The user record exists with the expected hashed password.
        $user = User::firstWhere('email', 'livewire@example.com');
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('StrongPass123!', $user->password));

        // Assert: Registration still fires the Registered event to keep onboarding hooks consistent.
        Event::assertDispatched(Registered::class, function (Registered $event) use ($user): bool {
            return $event->user->is($user);
        });
    }

    /** @test */
    public function validates_input_before_registering_via_livewire(): void
    {
        // Act: Attempt to submit invalid input via the Livewire form.
        Livewire::test('pages.auth.register')
            ->set('name', '')
            ->set('email', 'bad-email')
            ->set('password', 'short')
            ->set('password_confirmation', 'mismatch')
            ->call('register')
            ->assertHasErrors(['name', 'email', 'password']);

        // Assert: Confirm that no user was persisted when validation fails.
        $this->assertDatabaseCount('users', 0);
    }
}
