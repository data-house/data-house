<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminUserControllerTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_login_required(): void
    {
        $response = $this->get('/admin/users');

        $response->assertRedirectToRoute('login');
    }

    public function test_admin_role_required(): void
    {
        $user = User::factory()->guest()->create();

        $response = $this->actingAs($user)
            ->get('/admin/users');

        $response->assertForbidden();
    }

    public function test_user_listing_loads(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)
            ->get('/admin/users');

        $response->assertOk();

        $response->assertViewIs('admin.user.index');

        $response->assertViewHas('users');
    }
}
