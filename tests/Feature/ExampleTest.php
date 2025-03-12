<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{

    public function test_home_redirects_to_dashboard(): void
    {
        $response = $this->get('/');

        $response->assertRedirectToRoute('dashboard');
    }

    public function test_login_route_loads(): void
    {
        $response = $this->get(route('login'));

        $response->assertSuccessful();
    }
}
