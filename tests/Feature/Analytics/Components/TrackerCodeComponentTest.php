<?php

namespace Tests\Feature\Analytics\Components;

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
                'tracking' => [
                    'user' => true,
                    'guest' => true,
                ],
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
                'tracking' => [
                    'user' => true,
                    'guest' => true,
                ],
            ]
        ]);
        
        $view = $this->blade(
            '<x-analytics::tracking-code />'
        );

        $view->assertDontSee('analytics.localhost');
        $view->assertDontSee('asite');
    }
    
   
}
