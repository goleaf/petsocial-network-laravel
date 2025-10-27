<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Tests\Support\TestingVerifyCsrfToken;

/**
 * Feature level coverage for the CSRF verification middleware.
 */
it('rejects form submissions that omit the CSRF token', function () {
    // Register a temporary route that sits behind the web middleware stack.
    Route::post('/feature-csrf-protected', function () {
        return response()->json(['status' => 'protected']);
    })->middleware(['web', TestingVerifyCsrfToken::class]);
    // The testing middleware override disables Laravel's unit-test bypass so CSRF validation still runs.

    // Start the session to ensure the middleware has a token to compare against.
    Session::start();

    // Attempt to post without providing a token so the middleware can block it.
    $response = $this->withSession(['_token' => Session::token()])->post('/feature-csrf-protected', ['payload' => 'value']);

    // The request should fail with a 419 page expired status because the token is missing.
    $response->assertStatus(419);
});

it('accepts form submissions when the CSRF token is supplied', function () {
    // Expose another temporary route guarded by the CSRF middleware.
    Route::post('/feature-csrf-allowed', function () {
        return response()->json(['status' => 'ok']);
    })->middleware(['web', TestingVerifyCsrfToken::class]);
    // The override keeps the CSRF validation active even while the suite runs under the testing environment.

    // Start the session to generate a CSRF token the request can reuse.
    Session::start();
    $token = Session::token();

    // Post the payload while echoing the token in both the session and form data.
    $response = $this->withSession(['_token' => $token])->post('/feature-csrf-allowed', [
        '_token' => $token,
        'payload' => 'value',
    ]);

    // The middleware should validate the token and allow the request through successfully.
    $response->assertSuccessful()->assertJson(['status' => 'ok']);
});
