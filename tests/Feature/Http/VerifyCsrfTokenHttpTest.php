<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Tests\Support\TestingVerifyCsrfToken;

/**
 * HTTP request coverage for CSRF validation behaviours.
 */
it('returns 419 when JSON requests omit CSRF credentials', function () {
    // Register a JSON endpoint that stays inside the web middleware group.
    Route::post('/http-csrf-guarded', function () {
        return response()->json(['status' => 'protected']);
    })->middleware(['web', TestingVerifyCsrfToken::class]);
    // The testing middleware override ensures CSRF checks run even though the suite executes under the testing environment.

    // Submit the request without any headers or cookies so the middleware blocks it.
    $response = $this->postJson('/http-csrf-guarded', ['payload' => 'value']);

    // A missing token should trigger the page expired status code.
    $response->assertStatus(419);
});

it('permits JSON requests when CSRF headers and cookies align', function () {
    // Stand up another JSON endpoint protected by the same middleware stack.
    Route::post('/http-csrf-allowed', function () {
        return response()->json(['status' => 'ok']);
    })->middleware(['web', TestingVerifyCsrfToken::class]);
    // The override keeps CSRF validation enabled so JSON requests must provide valid credentials to succeed.

    // Prime the session to generate the synchronised token.
    Session::start();
    $token = Session::token();

    // Send the request with matching cookie, header, and session values so it authenticates.
    $response = $this->withSession(['_token' => $token])
        ->withCookie('XSRF-TOKEN', $token)
        ->withHeader('X-CSRF-TOKEN', $token)
        ->postJson('/http-csrf-allowed', ['payload' => 'value']);

    // The middleware should recognise the aligned credentials and allow the request through.
    $response->assertOk()->assertJson(['status' => 'ok']);
});
