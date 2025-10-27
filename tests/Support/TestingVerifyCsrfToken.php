<?php

namespace Tests\Support;

use App\Http\Middleware\VerifyCsrfToken;

/**
 * Test-only middleware that forces CSRF validation even in the unit testing environment.
 */
class TestingVerifyCsrfToken extends VerifyCsrfToken
{
    /**
     * Disable the unit test bypass so assertions exercise full CSRF verification.
     */
    protected function runningUnitTests(): bool
    {
        return false;
    }
}
