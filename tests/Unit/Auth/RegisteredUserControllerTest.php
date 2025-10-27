<?php

namespace Tests\Unit\Auth;

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Unit tests target the controller methods directly without routing helpers.
 */
class RegisteredUserControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function create_method_returns_the_expected_view(): void
    {
        // Arrange: Instantiate the controller under test.
        $controller = new RegisteredUserController();

        // Act: Call the create method directly to retrieve the view response.
        $view = $controller->create();

        // Assert: The view name aligns with the Blade template powering registration.
        $this->assertSame('auth.register', $view->name());
    }

    /** @test */
    public function store_method_creates_and_logs_in_a_user(): void
    {
        // Arrange: Fake hashing and events so we can assert the interactions precisely.
        Event::fake([Registered::class]);
        Auth::shouldReceive('login')->once();

        $controller = new RegisteredUserController();

        // Act: Build a request object mirroring a valid submission and call the method directly.
        $request = Request::create(route('register'), 'POST', [
            'name' => 'Unit Test User',
            'email' => 'unit@example.com',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
        ]);

        $response = $controller->store($request);

        // Assert: The persisted user matches the provided payload and uses a hashed password.
        $this->assertDatabaseHas('users', [
            'email' => 'unit@example.com',
        ]);
        $user = User::whereEmail('unit@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('StrongPass123!', $user->password));

        // Assert: The Registered event fired for the new account.
        Event::assertDispatched(Registered::class, function (Registered $event): bool {
            return $event->user instanceof User && $event->user->email === 'unit@example.com';
        });

        // Assert: The redirect target aligns with the configured home route.
        $this->assertSame(url(RouteServiceProvider::HOME), $response->getTargetUrl());
    }

    /** @test */
    public function store_method_validates_incoming_payload(): void
    {
        // Arrange: Suppress the default exception handling to catch validation feedback.
        $controller = new RegisteredUserController();
        $this->withoutExceptionHandling();

        // Act & Assert: Expect invalid input to trigger a validation exception when the method is invoked.
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The name field is required.');

        $request = Request::create(route('register'), 'POST', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ]);

        $controller->store($request);
    }
}
