<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Never reach real external APIs (e.g. Telegram) from the test suite; tests that need
        // HTTP fake it explicitly with Http::fake().
        Http::preventStrayRequests();
    }
}
