<?php

use Illuminate\Support\Facades\Route;

/**
 * HTTP tests ensuring JSON-centric requests trigger authentication errors instead of redirects.
 */
it('responds with an unauthorized status for JSON requests when unauthenticated', function () {
    // Define a JSON endpoint guarded by the Authenticate middleware to mirror API consumption.
    Route::middleware(['web', 'auth'])->get('/middleware/http-protected', function () {
        return response()->json(['message' => 'secure']);
    });

    // Perform the JSON request without authenticating to trigger the middleware guard.
    $response = $this->getJson('/middleware/http-protected');

    // Confirm the middleware responds with a 401 instead of redirecting to an HTML page.
    $response->assertUnauthorized();
});
