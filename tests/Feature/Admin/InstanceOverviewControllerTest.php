<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InstanceOverviewControllerTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_admin_area_requires_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirectToRoute('login');
    }

    public function test_overview_requires_admin_role(): void
    {
        $user = User::factory()->guest()->create();

        $response = $this->actingAs($user)
            ->get('/admin');

        $response->assertForbidden();
    }

    public function test_overview_loads(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)
            ->get('/admin');

        $response->assertOk();

        $response->assertViewIs('admin.dashboard');

        $response->assertSee('Users');

        $response->assertSee('Projects');
        
        $response->assertSee('Documents');
        
        $response->assertViewHas('total_users', 1);
        
        $response->assertViewHas('total_projects', 0);

        $response->assertViewHas('total_documents', 0);
    }
}
