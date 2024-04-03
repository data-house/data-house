<?php

namespace Tests\Feature\Concepts;

use App\Models\Concept;
use App\Models\ConceptCollection;
use App\Models\ConceptRelationType;
use App\Models\ConceptScheme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConceptTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_concepts_in_scheme(): void
    {
        $user = User::factory()->create();

        Concept::factory()->recycle($user)->create(); // used to ensure concept_id != concept_collection_id != 1

        $concept = Concept::factory()->recycle($user)->create();

        $scheme = ConceptScheme::factory()->recycle($user)->create();

        $concept->schemes()->attach($scheme);

        $this->assertDatabaseCount('concept_in_schemes', 1);

        $this->assertDatabaseHas('concept_in_schemes', [
            'concept_id' => $concept->getKey(),
            'concept_scheme_id' => $scheme->getKey(),
        ]);

        $this->assertTrue($scheme->concepts()->first()->is($concept));
    }
    
    public function test_concepts_in_collection(): void
    {
        $user = User::factory()->create();

        Concept::factory()->recycle($user)->create(); // used to ensure concept_id != concept_collection_id != 1

        $concept = Concept::factory()->recycle($user)->create();

        $collection = ConceptCollection::factory()->recycle($user)->create();

        $concept->collections()->attach($collection);

        $this->assertDatabaseCount('concept_collection_members', 1);

        $this->assertDatabaseHas('concept_collection_members', [
            'concept_id' => $concept->getKey(),
            'concept_collection_id' => $collection->getKey(),
        ]);

        $this->assertTrue($collection->concepts()->first()->is($concept));
    }
    
    public function test_concept_relationships(): void
    {
        $user = User::factory()->create();

        $concept = Concept::factory()->recycle($user)->create(); // used to ensure concept_id != concept_collection_id != 1

        $concept->relatesTo()->attach($related = Concept::factory()->recycle($user)->create());

        $concept->narrower()->attach($narrower = Concept::factory()->recycle($user)->create());
        
        $concept->broader()->attach($broader = Concept::factory()->recycle($user)->create());
        

        $this->assertDatabaseCount('concept_relationships', 3);

        $this->assertCount(3, $concept->belongsToConcepts);

        dump($concept->belongsToConcepts->toArray());

        // $this->assertTrue($collection->concepts()->first()->is($concept));
    }
}
