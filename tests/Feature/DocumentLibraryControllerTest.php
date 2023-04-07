<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DocumentLibraryControllerTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_library_access_requires_login(): void
    {
        $response = $this->get('/library');

        $response->assertRedirectToRoute('login');
    }
    
    public function test_library_page_loads(): void
    {
        $user = User::factory()->withPersonalTeam()->guest()->create();

        $response = $this->actingAs($user)
            ->get('/library');

        $response->assertOk();

        $response->assertViewIs('library.index');

        $response->assertSee('Search');
        
        $response->assertSee('Upload Document');
    }
}
