<?php

namespace Tests\Feature\Controllers {

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Lightweight controller dedicated to exercising the base Controller validation helpers during the feature test.
 */
class ValidationProbeController extends Controller
{
    public function __invoke(Request $request): array
    {
        // Use the validates requests trait to guard incoming payloads and return the cleaned data in the JSON response.
        $validated = $this->validate($request, [
            'name' => ['required', 'string', 'min:3'],
        ]);

        return [
            'validated' => $validated['name'],
        ];
    }
}

}

namespace {

use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;
use Tests\Feature\Controllers\ValidationProbeController;

it('validates requests through the base controller helper when handling feature-level HTTP calls', function (): void {
    // Register a temporary route targeting the probe controller so we can exercise validation end-to-end.
    Route::middleware('web')->post('/controller-validation-probe', ValidationProbeController::class);

    // The first request omits the required name to confirm the trait triggers a standard validation error response.
    $this->postJson('/controller-validation-probe', [])->assertUnprocessable()->assertJsonValidationErrors(['name']);

    // The second request provides a valid payload to confirm the validated data flows into the JSON response.
    $this->postJson('/controller-validation-probe', ['name' => 'Fido'])->assertSuccessful()->assertJson([
        'validated' => 'Fido',
    ]);

    // Reset the router after the assertion so additional tests are not polluted by the temporary endpoint.
    app('router')->setRoutes(new RouteCollection());
});

}
