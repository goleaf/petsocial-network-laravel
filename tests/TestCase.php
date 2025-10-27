<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Base test case to share common helpers across the suite.
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}
