<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Base test case to share common helpers across the suite.
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Prepare the in-memory SQLite schema before each test executes.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Call the Pest bootstrap helpers when available so PHPUnit-driven tests share the same schema.
        if (function_exists('prepareTestDatabase')) {
            prepareTestDatabase();
        }

        if (function_exists('preparePetNotificationSchema')) {
            preparePetNotificationSchema();
        }
    }
}
