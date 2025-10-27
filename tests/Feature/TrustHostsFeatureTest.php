<?php

use App\Http\Middleware\TrustHosts;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

it('specifies trusted hosts when the middleware is forced to execute during a feature flow', function (): void {
    // Configure a realistic production-like host for the middleware to trust.
    config(['app.url' => 'https://petsocial.test']);

    // Simulate an HTTP request that will be inspected by the middleware.
    $request = Request::create(
        uri: '/feature/trusted-hosts',
        method: 'GET',
        server: ['HTTP_HOST' => 'petsocial.test']
    );

    // Force the middleware to run even though the test environment normally bypasses it.
    $middleware = new class(app()) extends TrustHosts {
        protected function shouldSpecifyTrustedHosts(): bool
        {
            return true;
        }
    };

    $trustedHosts = null;

    try {
        $middleware->handle($request, function (Request $handledRequest) use (&$trustedHosts) {
            // Capture the trusted host configuration so the assertion can verify the expected regex.
            $trustedHosts = SymfonyRequest::getTrustedHosts();

            return response()->noContent();
        });
    } finally {
        // Always reset the trusted hosts to avoid affecting other tests that rely on Symfony internals.
        SymfonyRequest::setTrustedHosts([]);
    }

    expect($trustedHosts)
        ->toBeArray()
        ->toContain('{^(.+\\.)?petsocial\\.test$}i');
});
