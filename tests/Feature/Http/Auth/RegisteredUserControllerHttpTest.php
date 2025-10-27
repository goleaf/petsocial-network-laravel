<?php

namespace Tests\Feature\Http\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * HTTP layer tests verify status codes, redirects, and localized responses.
 */
class RegisteredUserControllerHttpTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function get_request_to_register_returns_ok(): void
    {
        // Arrange: Disable Vite asset resolution to avoid missing manifest errors.
        $this->withoutVite();

        // Act: Perform a GET request against the registration route.
        $response = $this->get(route('register'));

        // Assert: The response is successful and includes the localized heading text.
        $response->assertOk();
        $response->assertSee(Lang::get('auth.join_community'));
    }

    /** @test */
    public function post_request_redirects_on_success(): void
    {
        // Arrange: Prevent notification dispatches and bypass Vite asset loading.
        Notification::fake();
        $this->withoutVite();

        // Act: Submit a valid POST request.
        $response = $this->post(route('register'), [
            'name' => 'HTTP Layer User',
            'email' => 'http@example.com',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
        ]);

        // Assert: The response redirects to the dashboard with a 302 status.
        $response->assertRedirect(route('dashboard'));
        $response->assertStatus(302);
    }

    /** @test */
    public function post_request_with_invalid_payload_returns_errors(): void
    {
        // Arrange: Avoid Vite manifest lookups while posting invalid data.
        $this->withoutVite();

        // Act: Submit malformed data while staying on the form.
        $response = $this->from(route('register'))->post(route('register'), [
            'name' => '',
            'email' => 'broken-email',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ]);

        // Assert: Validation errors are present and the visitor remains on the registration page.
        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }
}
