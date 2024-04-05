<?php

namespace Tests\Feature\Concepts;

use App\Models\Concept;
use App\Models\ConceptCollection;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TaxonomyImportCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function configureTopics(array $content)
    {
        Topic::clear();
        
        Storage::fake('local');

        Storage::put('topics.json', json_encode($content));

        config([
            'library.topics.file' => 'topics.json',
        ]);
    }

    
    public function test_existing_topics_can_be_imported_into_concepts(): void
    {
        $this->configureTopics([
            "Area" => [
                "id" => "area",
                "name" => "Area",
                "children" => [
                    [
                        "id" => "energy",
                        "name" => "Energy"
                    ],
                    [
                        "id" => "industry",
                        "name" => "Industry"
                    ]
                ]
            ],
        ]);

        $user = User::factory()->admin()->create();


        $this->artisan('taxonomy:import-topics')
            ->assertSuccessful();


        $collection = ConceptCollection::first();

        $this->assertNotNull($collection);

        $this->assertEquals('Area', $collection->title);
        $this->assertNull($collection->description);
        
        $this->assertEquals(3, Concept::count());

        $conceptsInCollection = $collection->concepts;
        
        $this->assertEquals(2, $conceptsInCollection->count());

        $this->assertEquals(['Energy', 'Industry'], $conceptsInCollection->pluck('title')->values()->toArray());

        $areaConcept = Concept::labelled('Area')->first();

        $this->assertNotNull($areaConcept);

        $narrowerConceptsOfArea = $areaConcept->broader; // give me the concept for which the area concept is broader of

        $this->assertEquals(2, $narrowerConceptsOfArea->count());

        $concept = $narrowerConceptsOfArea->first();

        $this->assertEquals('Energy', $concept->title);
        $this->assertEquals('energy', $concept->alternateLabel);
        $this->assertNull($concept->description);
    }
    
    // public function test_mutiple_hierarchies_imported_into_concepts(): void
    // {
    //     $this->configureTopics([
    //         "Area" => [
    //             "id" => "area",
    //             "name" => "Area",
    //             "children" => [
    //                 [
    //                     "id" => "energy",
    //                     "name" => "Energy",
    //                     "children" => [
    //                         [
    //                             "id" => "ee",
    //                             "name" => "Energy Efficiency",
    //                         ],
    //                         [
    //                             "id" => "re",
    //                             "name" => "Renewable Energy",
    //                         ],
    //                     ]
    //                 ],
    //                 [
    //                     "id" => "industry",
    //                     "name" => "Industry"
    //                 ]
    //             ]
    //         ],
    //     ]);

    //     $user = User::factory()->admin()->create();


    //     $this->artisan('taxonomy:import-topics')
    //         ->assertSuccessful();


    //     $collection = ConceptCollection::first();

    //     $this->assertNotNull($collection);

    //     $this->assertEquals('Area', $collection->title);
    //     $this->assertNull($collection->description);
        

    //     $concepts = $collection->concepts;
        
    //     $this->assertEquals(4, $concepts->count());

    //     $concept = $concepts->first();

    //     $this->assertEquals('Energy', $concept->title);
    //     $this->assertEquals('energy', $concept->alternateLabel);
    //     $this->assertNull($concept->description);
    // }
}
