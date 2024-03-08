<?php

namespace Tests\Feature\Analytics;

use App\Analytics\AnalyticsManager;
use App\Analytics\Drivers\MatomoDriver;
use App\Analytics\Drivers\NullDriver;
use App\Analytics\Facades\Analytics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnalyticsManagerTest extends TestCase
{

    public function test_default_driver_falls_back_to_smalot_library(): void
    {
        config(['analytics.default' => null]);

        $driver = app()->make(AnalyticsManager::class)->getDefaultDriver();

        $this->assertEquals('null', $driver);
    }
    
    public function test_default_driver_respect_configuration(): void
    {

        config(['analytics.default' => 'matomo']);

        $driver = app()->make(AnalyticsManager::class)->getDefaultDriver();

        $this->assertEquals('matomo', $driver);
    }
    
    public function test_null_driver_can_be_created(): void
    {
        $driver = Analytics::driver('null');

        $this->assertInstanceOf(NullDriver::class, $driver);
    }
    
    public function test_matomo_driver_can_be_created(): void
    {
        $driver = Analytics::driver('matomo');

        $this->assertInstanceOf(MatomoDriver::class, $driver);
    }
    
}
