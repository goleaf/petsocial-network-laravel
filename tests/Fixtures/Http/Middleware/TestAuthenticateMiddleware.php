<?php

namespace Tests\Fixtures\Http\Middleware;

use App\Http\Middleware\Authenticate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;

/**
 * Concrete authenticate middleware that exposes the redirect target for unit assertions.
 */
class TestAuthenticateMiddleware extends Authenticate
{
    public function __construct(AuthFactory $auth)
    {
        // Initialise the parent middleware with the application's authentication factory.
        parent::__construct($auth);
    }

    public function redirectEndpoint(Request $request): ?string
    {
        // Proxy to the original redirectTo method so tests can inspect its return value.
        return $this->redirectTo($request);
    }
}
