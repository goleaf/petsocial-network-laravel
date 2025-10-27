<?php

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;

/**
 * Unit coverage for the VerifyCsrfToken middleware configuration.
 */
it('ships with an empty CSRF exception list by default', function () {
    // Resolve the middleware from the container so configuration mirrors production usage.
    $middleware = app(VerifyCsrfToken::class);

    // Reflect on the protected $except property to inspect the URIs that bypass CSRF checks.
    $reflection = new ReflectionClass($middleware);
    $property = $reflection->getProperty('except');
    $property->setAccessible(true);

    // Capture the current exception array for verification.
    $exceptions = $property->getValue($middleware);

    // The array should exist but remain empty to keep all routes behind CSRF protection.
    expect($exceptions)->toBeArray()->and($exceptions)->toBeEmpty();
});

it('respects runtime updates to the exception list', function () {
    // Resolve the middleware so the reflection helper can manipulate its protected members.
    $middleware = app(VerifyCsrfToken::class);

    $reflection = new ReflectionClass($middleware);
    $property = $reflection->getProperty('except');
    $property->setAccessible(true);

    // Update the exception list to simulate bypassing CSRF for a webhook endpoint.
    $property->setValue($middleware, ['/webhook/callback']);

    $method = $reflection->getMethod('inExceptArray');
    $method->setAccessible(true);

    // Create a fake request to the webhook URI to ensure the exception takes effect.
    $request = Request::create('/webhook/callback', 'POST');

    // The middleware should detect the URI in the exception list.
    expect($method->invoke($middleware, $request))->toBeTrue();
});
