<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\View\Components\AddDocumentsButton;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\TransientToken;
use Illuminate\Support\Str;
use Tests\TestCase;

class AddDocumentsButtonTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_document_button_renderable(): void
    {
        config(['library.upload.allow_direct_upload' => true]);

        $user = User::factory()->manager()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $view = $this->actingAs($user)->component(AddDocumentsButton::class);
 
        $view->assertSee('Upload Document');

        $view->assertSee('Imports');
    }
    
    public function test_add_single_document_button_not_available_when_disabled(): void
    {
        config(['library.upload.allow_direct_upload' => false]);

        $user = User::factory()->manager()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $view = $this->actingAs($user)->component(AddDocumentsButton::class);
 
        $view->assertDontSee('Upload Document');

        $view->assertDontSee('View folder');

        $view->assertSee('Imports');
    }

    public function test_add_document_button_not_rendered_when_user_cannot_upload_or_import(): void
    {
        $user = User::factory()->guest()->create();

        $view = $this->actingAs($user)->component(AddDocumentsButton::class);
 
        $view->assertDontSee('Upload Document');
        
        $view->assertDontSee('View folder');

        $view->assertDontSee('Imports');
    }
    
    public function test_upload_link_rendered_when_direct_upload_disabled(): void
    {
        config(['library.upload.allow_direct_upload' => false]);

        $user = User::factory()->manager()->withPersonalTeam([
            'settings' => [
                'upload' => [
                    'uploadLinkUrl' => 'http://link.localhost',
                    'supportProjects' => false,
                ]
            ]
            
        ])->create();
        
        $view = $this->actingAs($user)->component(AddDocumentsButton::class);
 
        $view->assertSee('View folder');
        
        $view->assertSee('http://link.localhost');
    }

    public function test_upload_link_not_rendered_if_project_is_not_whitelisted(): void
    {
        config(['library.upload.allow_direct_upload' => false]);

        $project = Project::factory()->create([
                'title' => 'Project title',
                'slug' => 'project-slug',
            ]);

        $user = User::factory()->manager()->withPersonalTeam([
            'settings' => [
                'upload' => [
                    'uploadLinkUrl' => 'http://link.localhost',
                    'supportProjects' => false,
                    'limitProjectsTo' => Str::ulid(),
                ]
            ]
            
        ])->create();
        
        $view = $this->actingAs($user)->component(AddDocumentsButton::class, [
            'project' => $project,
        ]);
 
        $view->assertDontSee('View folder');
        
        $view->assertDontSee('http://link.localhost');
    }
    
    public function test_upload_link_rendered_with_project_support(): void
    {
        config(['library.upload.allow_direct_upload' => false]);

        $user = User::factory()->manager()->withPersonalTeam([
            'settings' => [
                'upload' => [
                    'uploadLinkUrl' => 'http://link.localhost',
                    'supportProjects' => true,
                ]
            ]
            
        ])->create();

        $project = Project::factory()->hasAttached($user->currentTeam, [], 'teams')->create([
            'title' => 'Project title',
            'slug' => 'project-slug',
        ]);
        
        $view = $this->actingAs($user)->component(AddDocumentsButton::class, [
            'project' => $project,
        ]);
 
        $view->assertSee('View folder');
        
        $view->assertSee('http://link.localhost?path=' . urlencode('/Project title [project-slug]'));
    }
    
    public function test_upload_link_rendered_with_project_handle_null_project(): void
    {
        config(['library.upload.allow_direct_upload' => false]);

        $user = User::factory()->manager()->withPersonalTeam([
            'settings' => [
                'upload' => [
                    'uploadLinkUrl' => 'http://link.localhost',
                    'supportProjects' => true,
                ]
            ]
            
        ])->create();
        
        $view = $this->actingAs($user)->component(AddDocumentsButton::class);
 
        $view->assertSee('View folder');
        
        $view->assertSee('http://link.localhost');
        $view->assertDontSee('path=');
    }
    
    public function test_upload_link_rendered_consider_team_managed_projects(): void
    {
        config(['library.upload.allow_direct_upload' => false]);

        $user = User::factory()->manager()->withPersonalTeam([
            'settings' => [
                'upload' => [
                    'uploadLinkUrl' => 'http://link.localhost',
                    'supportProjects' => true,
                ]
            ]
            
        ])->create();

        $project = Project::factory()->hasAttached($user->currentTeam, ['role' => 'admin'], 'teams')->create([
            'title' => 'Project title',
            'slug' => 'project-slug',
        ]);

        $this->assertTrue($user->currentTeam->fresh()->managedProjects()->first()->is($project));
        
        $view = $this->actingAs($user)->component(AddDocumentsButton::class);
 
        $view->assertSee('View folder');
        
        $view->assertSee('http://link.localhost?path=' . urlencode('/Project title [project-slug]'));
    }
    
    public function test_upload_link_rendered_does_not_consider_team_managed_projects_when_team_not_owner(): void
    {
        config(['library.upload.allow_direct_upload' => false]);

        $user = User::factory()->manager()->withPersonalTeam([
            'settings' => [
                'upload' => [
                    'uploadLinkUrl' => 'http://link.localhost',
                    'supportProjects' => true,
                ]
            ]
            
        ])->create();

        $project = Project::factory()->hasAttached($user->currentTeam, ['role' => 'guest'], 'teams')->create([
            'title' => 'Project title',
            'slug' => 'project-slug',
        ]);
        
        $view = $this->actingAs($user)->component(AddDocumentsButton::class);
 
        $view->assertSee('View folder');
        
        $view->assertSee('http://link.localhost');
        $view->assertDontSee('path=');
    }
    
    public function test_upload_link_not_rendered_when_direct_upload_possible(): void
    {
        config(['library.upload.allow_direct_upload' => true]);

        $user = User::factory()->manager()->withPersonalTeam([
            'settings' => [
                'upload' => [
                    'uploadLinkUrl' => 'http://link.localhost',
                    'supportProjects' => false,
                ]
            ]
        ])->create();
        
        $view = $this->actingAs($user)->component(AddDocumentsButton::class);
 
        $view->assertSee('Upload Document');
        
        $view->assertDontSee('http://link.localhost');
    }
}
