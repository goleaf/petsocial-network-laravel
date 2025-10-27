<?php

namespace Tests\Http\Controllers {

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Controller dedicated to validating the authorization helpers provided by the base controller during HTTP tests.
 */
class AuthorizationProbeController extends Controller
{
    public function __invoke(Request $request): array
    {
        // Exercise the authorize helper to ensure the trait continues to integrate with Laravel's gate system.
        $this->authorize('viewInternalDashboard');

        return [
            'allowed' => true,
        ];
    }
}

}

namespace {

use App\Models\User;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Tests\Http\Controllers\AuthorizationProbeController;

use function Pest\Laravel\actingAs;

it('enforces authorization checks supplied by the base controller helpers', function (): void {
    // Define a Gate ability that will be used by the authorize helper during the HTTP interactions.
    Gate::define('viewInternalDashboard', static fn (User $user): bool => $user->role === 'admin');

    // Register a temporary route that points to the probe controller so we can issue HTTP requests against it.
    Route::middleware('web')->get('/controller-authorization-probe', AuthorizationProbeController::class);

    // Authenticate as a standard member and verify the controller blocks access because the gate denies the action.
    $member = User::factory()->create(['role' => 'user']);
    actingAs($member);
    $this->getJson('/controller-authorization-probe')->assertForbidden();

    // Authenticate as an administrator and confirm the controller allows the request to succeed once authorized.
    $admin = User::factory()->create(['role' => 'admin']);
    actingAs($admin);
    $this->getJson('/controller-authorization-probe')->assertSuccessful()->assertJson([
        'allowed' => true,
    ]);

    // Clean up the temporary route so other tests execute against a pristine environment.
    app('router')->setRoutes(new RouteCollection());
});

}
