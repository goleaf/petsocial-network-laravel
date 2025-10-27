<?php

use App\Http\Middleware\TrustHosts;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

it('blocks untrusted hostnames during the HTTP pipeline', function (): void {
    // Mirror a production URL so the middleware generates the trusted regex correctly.
    config(['app.url' => 'https://petsocial.test']);

    // Craft a request that mimics a malicious host attempting to reach the application.
    $request = Request::create(
        uri: '/http/trusted-hosts',
        method: 'GET',
        server: ['HTTP_HOST' => 'malicious.test']
    );

    // Override the environment guard so the middleware actually sets trusted hosts within the test harness.
    $middleware = new class(app()) extends TrustHosts {
        protected function shouldSpecifyTrustedHosts(): bool
        {
            return true;
        }
    };

    try {
        $middleware->handle($request, function (Request $handledRequest) {
            // Accessing the host should trigger Symfony's SuspiciousOperationException for untrusted hosts.
            $handledRequest->getHost();
        });
    } finally {
        // Reset the static trusted hosts registry to keep other HTTP tests isolated.
        SymfonyRequest::setTrustedHosts([]);
    }
})->throws(SuspiciousOperationException::class, 'Untrusted Host "malicious.test".');
