<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;
use Saloon\Config;
use Saloon\Http\Faking\MockClient;
use Saloon\MockConfig;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
 
        $this->withoutVite();

        Config::preventStrayRequests();
        MockClient::destroyGlobal();
        MockConfig::throwOnMissingFixtures();
        Http::preventStrayRequests();
    }
}
