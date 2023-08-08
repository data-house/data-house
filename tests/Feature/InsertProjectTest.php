<?php

namespace Tests\Feature;

use App\Actions\Project\InsertProject;
use App\Models\Project;
use App\Models\ProjectType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\ValidationException;
use PrinsFrank\Standards\Country\CountryAlpha3;
use Tests\TestCase;
use Throwable;

class InsertProjectTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_project_created(): void
    {
        /**
         * @var \App\Actions\Project\InsertProject
         */
        $insert = app()->make(InsertProject::class);

        $project = $insert([
            'title' => 'Test project',
            'type' => 10,
            'topics' => ['test topic'],
            'countries' => ['DEU'],
            'organizations' => ['implementers' => ['test org']],
            'properties' => ['a-property' => 'a-value'],
            'slug' => 'test-project',
            'description' => 'sample description',
            'starts_at' => '2023-08-01',
            'ends_at' => '2023-08-31',
            'status' => 10,
        ]);

        $this->assertNotNull($project);

        $this->assertEquals('Test project', $project->title);
        $this->assertEquals(ProjectType::BILATERAL, $project->type);
        $this->assertEquals(collect(['test topic']), $project->topics);
        $this->assertEquals(CountryAlpha3::Germany, $project->countries->first());
        $this->assertEquals(collect(['implementers' => ['test org']]), $project->organizations);
        $this->assertEquals('test-project', $project->slug);
        $this->assertEquals('sample description', $project->description);
        $this->assertEquals('2023-08-01', $project->starts_at->toDateString());
        $this->assertEquals('2023-08-31', $project->ends_at->toDateString());
    }
    
    public function test_project_slug_generated_from_title_when_missing(): void
    {
        /**
         * @var \App\Actions\Project\InsertProject
         */
        $insert = app()->make(InsertProject::class);

        $project = $insert([
            'title' => 'Test project',
            'type' => 10,
            'topics' => ['test topic'],
            'countries' => ['DEU'],
            'organizations' => ['implementers' => ['test org']],
            'properties' => ['a-property' => 'a-value'],
            'slug' => null,
            'status' => 10,
        ]);

        $this->assertNotNull($project);

        $this->assertEquals('Test project', $project->title);
        $this->assertEquals(ProjectType::BILATERAL, $project->type);
        $this->assertEquals(collect(['test topic']), $project->topics);
        $this->assertEquals(CountryAlpha3::Germany, $project->countries->first());
        $this->assertEquals(collect(['implementers' => ['test org']]), $project->organizations);
        $this->assertEquals('test-project', $project->slug);
        $this->assertNull($project->description);
        $this->assertNull($project->starts_at);
        $this->assertNull($project->ends_at);
    }
    
    public function test_project_title_unique(): void
    {

        Project::factory()->create([
            'title' => 'Test project'
        ]);

        /**
         * @var \App\Actions\Project\InsertProject
         */
        $insert = app()->make(InsertProject::class);

        try {
            $project = $insert([
                'title' => 'Test project',
                'type' => 10,
                'topics' => ['test topic'],
                'countries' => ['DEU'],
                'organizations' => ['implementers' => ['test org']],
                'properties' => ['a-property' => 'a-value'],
                'slug' => null,
                'status' => 10,
            ]);
        } catch (Throwable $th) {
            $this->assertInstanceOf(ValidationException::class, $th);

            $this->assertEquals("The title has already been taken.", $th->getMessage());
        }
    }
    
    public function test_project_slug_unique(): void
    {

        Project::factory()->create([
            'title' => 'Test project',
            'slug' => 'test-slug',
        ]);

        /**
         * @var \App\Actions\Project\InsertProject
         */
        $insert = app()->make(InsertProject::class);

        try {
            $project = $insert([
                'title' => 'Another project',
                'type' => 10,
                'topics' => ['test topic'],
                'countries' => ['DEU'],
                'organizations' => ['implementers' => ['test org']],
                'properties' => ['a-property' => 'a-value'],
                'slug' => 'test-slug',
                'status' => 10,
            ]);
        } catch (Throwable $th) {
            $this->assertInstanceOf(ValidationException::class, $th);

            $this->assertEquals("The slug has already been taken.", $th->getMessage());
        }
    }
}
