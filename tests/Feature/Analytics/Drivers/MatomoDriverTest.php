<?php

namespace Tests\Feature\Analytics\Drivers;

use App\Analytics\Drivers\MatomoDriver;
use App\Models\User;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MatomoDriverTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_tracking_code_returned_if_configuration_missing(): void
    {
        $driver = new MatomoDriver();

        $tracking = $driver->trackerCode();

        $this->assertInstanceOf(Htmlable::class, $tracking);
        $this->assertEquals('', $tracking->toHtml());
    }
    
    public function test_guest_tracking_disabled(): void
    {
        $driver = new MatomoDriver([
            'host' => 'analytics.localhost',
            'tracker_endpoint' => 'endpoint.php',
            'tracker_script' => 'script.js',
            'site_id' => 'site',
            'tracking' => [],
        ]);

        $tracking = $driver->trackerCode()->toHtml();

        $this->assertEmpty($tracking);
    }
    
    public function test_tracking_code_returned(): void
    {
        $driver = new MatomoDriver([
            'host' => 'analytics.localhost',
            'tracker_endpoint' => 'endpoint.php',
            'tracker_script' => 'script.js',
            'site_id' => 'site',
            'tracking' => [
                'guest' => true,
            ],
        ]);

        $tracking = $driver->trackerCode()->toHtml();

        $this->assertNotEmpty($tracking);

        $this->assertStringContainsString('analytics.localhost', $tracking);
        $this->assertStringContainsString('endpoint.php', $tracking);
        $this->assertStringContainsString('script.js', $tracking);
        $this->assertStringContainsString("'setSiteId', 'site'", $tracking);
    }
    
    public function test_tracking_code_handle_guest_user_when_user_tracking_enabled(): void
    {
        $driver = new MatomoDriver([
            'host' => 'analytics.localhost',
            'tracker_endpoint' => 'endpoint.php',
            'tracker_script' => 'script.js',
            'site_id' => 'site',
            'tracking' => [
                'user' => true,
                'guest' => true,
            ],
        ]);

        $tracking = $driver->trackerCode()->toHtml();

        $this->assertNotEmpty($tracking);

        $this->assertStringContainsString('analytics.localhost', $tracking);
        $this->assertStringContainsString('endpoint.php', $tracking);
        $this->assertStringContainsString('script.js', $tracking);
        $this->assertStringContainsString("'setSiteId', 'site'", $tracking);
        $this->assertStringNotContainsString('setUserId', $tracking);
    }
    
    public function test_user_tracking_enabled(): void
    {
        $user = User::factory()->guest()->create();

        $driver = new MatomoDriver([
            'host' => 'analytics.localhost',
            'tracker_endpoint' => 'endpoint.php',
            'tracker_script' => 'script.js',
            'site_id' => 'site',
            'tracking' => [
                'user' => true,
                'guest' => false,
            ],
        ]);

        $this->actingAs($user);

        $tracking = $driver->trackerCode()->toHtml();

        $this->assertNotEmpty($tracking);

        $this->assertStringContainsString('analytics.localhost', $tracking);
        $this->assertStringContainsString('endpoint.php', $tracking);
        $this->assertStringContainsString('script.js', $tracking);
        $this->assertStringContainsString("'setSiteId', 'site'", $tracking);
        $this->assertStringContainsString('setUserId', $tracking);
    }
}
