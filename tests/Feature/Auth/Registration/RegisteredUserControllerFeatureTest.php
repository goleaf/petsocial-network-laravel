<?php

namespace Tests\Feature\Auth\Registration;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Feature coverage for the traditional registration controller endpoints.
 */
class RegisteredUserControllerFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function renders_the_registration_page_for_guests(): void
    {
        // Arrange: Disable Vite asset resolution so views render without manifest files.
        $this->withoutVite();

        // Act: Request the registration page as an unauthenticated visitor.
        $response = $this->get(route('register'));

        // Assert: The response is successful and renders localized onboarding copy.
        $response->assertOk();
        $response->assertSeeText(__('auth.join_community'));
    }

    /** @test */
    public function registers_a_new_user_and_logs_them_in(): void
    {
        // Arrange: Fake the Registered event so we can assert it later.
        Event::fake([Registered::class]);
        Notification::fake();
        $this->withoutVite();

        // Act: Submit a valid registration payload through the HTTP route.
        $response = $this->post(route('register'), [
            'name' => 'Feature Test User',
            'email' => 'feature@example.com',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
        ]);

        // Assert: The request redirects to the expected home route and authenticates the user.
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        // Assert: The user record exists with a hashed password.
        $user = User::whereEmail('feature@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('StrongPass123!', $user->password));

        // Assert: The Registered event fired to trigger welcome workflows.
        Event::assertDispatched(Registered::class, function (Registered $event) use ($user): bool {
            return $event->user->is($user);
        });
    }

    /** @test */
    public function rejects_invalid_registration_payloads(): void
    {
        // Act: Attempt to register with intentionally malformed input.
        $response = $this->from(route('register'))->post(route('register'), [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ]);

        // Assert: Validation errors are reported and the visitor is redirected back.
        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertGuest();

        // Assert: No user was created with the invalid payload.
        $this->assertDatabaseMissing('users', [
            'email' => 'not-an-email',
        ]);
    }
}
