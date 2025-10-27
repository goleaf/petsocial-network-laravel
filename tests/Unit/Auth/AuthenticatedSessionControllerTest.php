<?php

namespace Tests\Unit\Auth;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

/**
 * Unit tests for the authenticated session controller that focus on isolated logic branches.
 */
class AuthenticatedSessionControllerTest extends TestCase
{
    /**
     * Ensure Mockery expectations are cleaned up after each test.
     */
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    /**
     * The create action should return the login view so guests can authenticate.
     */
    public function test_create_returns_login_view(): void
    {
        // Instantiate the controller directly to bypass routing layers.
        $controller = new AuthenticatedSessionController();

        // Call the method and capture the returned view instance.
        $view = $controller->create();

        $this->assertSame('auth.login', $view->name());
    }

    /**
     * The destroy action should log out the guard and invalidate the session payload.
     */
    public function test_destroy_logs_out_guard_and_invalidates_session(): void
    {
        // Instantiate the controller and craft a synthetic request carrying a session.
        $controller = new AuthenticatedSessionController();
        $request = Request::create('/logout', 'POST');

        $session = $this->app['session.store'];
        $session->start();
        $session->put('example', 'value');
        $session->put('_token', 'original-token');
        $request->setLaravelSession($session);

        // Mock the guard so we can assert logout is requested on the correct driver.
        $guard = Mockery::mock(StatefulGuard::class);
        $guard->shouldReceive('logout')->once();

        Auth::shouldReceive('guard')->once()->with('web')->andReturn($guard);

        // Invoke the destroy action which should flush the session and redirect home.
        $response = $controller->destroy($request);

        $this->assertSame(url('/'), $response->getTargetUrl());
        $this->assertFalse($session->has('example'));
        $this->assertNotSame('original-token', $session->token());
    }
}
