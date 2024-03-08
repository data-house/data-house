<?php

namespace Tests\Feature\Analytics\Drivers;

use App\Analytics\Drivers\MatomoDriver;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TrackerCodeComponentTest extends TestCase
{
    
    
    public function test_tracking_code_rendered(): void
    {
        config([
            'analytics.default' => 'matomo',
            'analytics.processors.matomo' => [
                'host' => 'analytics.localhost',
                'tracker_endpoint' => 'endpoint.php',
                'tracker_script' => 'script.js',
                'site_id' => 'asite',
                'user_tracking' => true,
            ]
        ]);
        
        $view = $this->blade(
            '<x-analytics::tracking-code />'
        );

        $view->assertSee('analytics.localhost');
        $view->assertSee('asite');
    }
    
    public function test_null_configuration_handled(): void
    {
        config([
            'analytics.default' => 'matomo',
            'analytics.processors.matomo' => [
                'tracker_endpoint' => 'endpoint.php',
                'tracker_script' => 'script.js',
                'site_id' => 'asite',
                'user_tracking' => true,
            ]
        ]);
        
        $view = $this->blade(
            '<x-analytics::tracking-code />'
        );

        $view->assertDontSee('analytics.localhost');
        $view->assertDontSee('asite');
    }
    
   
}
