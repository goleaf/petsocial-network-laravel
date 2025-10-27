<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

/**
 * Bootstrap the Laravel application for test environments.
 */
trait CreatesApplication
{
    /**
     * Create the application instance for the test suite.
     */
    public function createApplication(): Application
    {
        $basePath = dirname(__DIR__);

        // When a root .env file is missing we redirect Laravel to the test stub
        // so Dotenv has a concrete file to parse without emitting warnings.
        $useStubEnvironment = ! file_exists($basePath.'/.env');

        $app = require $basePath.'/bootstrap/app.php';

        if ($useStubEnvironment) {
            // The stub lives under tests/environment/.env.testing and contains the
            // bare minimum configuration for the suite to boot successfully.
            $app->useEnvironmentPath(__DIR__.'/environment');
            $app->loadEnvironmentFrom('.env.testing');
        }

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
