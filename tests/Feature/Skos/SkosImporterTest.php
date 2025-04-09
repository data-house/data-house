<?php

namespace Tests\Feature\Skos;

use App\Models\SkosConcept;
use App\Models\SkosConceptScheme;
use App\Skos\SkosImporter;
use App\SkosRelationType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkosImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_sdg_scheme_imported(): void
    {
        $path = base_path('tests/fixtures/vocabularies/sdg.ttl');

        SkosImporter::importFromTurtleFile($path, '');

        $insertedScheme = SkosConceptScheme::first();

        $this->assertEquals('https://vocabulary.sdg/SDG', $insertedScheme->uri);
        $this->assertEquals('Sustainable Development Goals', $insertedScheme->pref_label);

        $concept = SkosConcept::query()
            ->where('uri', 'https://vocabulary.sdg/1.1')
            ->sole()
            ->load(['broader', 'narrower', 'related']);

        $this->assertInstanceOf(SkosConcept::class, $concept);

        $this->assertEquals('Target 1.1: By 2030, eradicate extreme poverty for all people everywhere, currently measured as people living on less than $1.25 a day', $concept->pref_label);
        $this->assertEquals(["Target 1.1"], $concept->alt_labels->all());
        $this->assertEquals('1.1', $concept->notation);
        $this->assertEquals('By 2030, eradicate extreme poverty for all people everywhere, currently measured as people living on less than $1.25 a day', $concept->definition);
        $this->assertEmpty($concept->hidden_labels);
        $this->assertNull($concept->note);
        $this->assertFalse($concept->top_concept);


        $this->assertCount(1, $concept->broader);
        $this->assertEquals("Goal 1: No poverty", $concept->broader->first()->pref_label);
        $this->assertTrue($concept->broader->first()->top_concept, "Goal 1 expected to be a top concept");
        $this->assertEquals(SkosRelationType::BROADER, $concept->broader->first()->pivot->type);

        $this->assertCount(1, $concept->narrower);
        $this->assertEquals("Indicator 1.1.1", $concept->narrower->first()->pref_label);
        $this->assertEquals(SkosRelationType::NARROWER, $concept->narrower->first()->pivot->type);

        $this->assertCount(0, $concept->related);


        $collection = $insertedScheme->collections()->first();

        $this->assertEquals('Goals', $collection->pref_label);
        
        $collectionMembers = $collection->concepts->pluck('notation');

        $this->assertEquals([
            "sdg1", "sdg2", "sdg3", "sdg4", "sdg5",
            "sdg6", "sdg7", "sdg8", "sdg9", "sdg10",
            "sdg11", "sdg12", "sdg13", "sdg14", "sdg15",
            "sdg16", "sdg17",
        ], $collectionMembers->toArray());

    }

    public function test_base_uri_deduced_from_default_namespace(): void
    {
        $onePath = base_path('tests/fixtures/vocabularies/scheme-one.ttl');

        SkosImporter::importFromTurtleFile($onePath);

        $this->assertEquals('https://one.scheme/', SkosConceptScheme::first()->vocabulary_base_uri);

        

    }

    public function test_cross_scheme_concepts_imported(): void
    {
        $onePath = base_path('tests/fixtures/vocabularies/scheme-one.ttl');
        $secondPath = base_path('tests/fixtures/vocabularies/scheme-two.ttl');

        SkosImporter::importFromTurtleFile($onePath, 'https://one.scheme/');

        SkosImporter::importFromTurtleFile($secondPath, 'https://two.scheme/');

        $this->assertEquals(2, SkosConceptScheme::count());

        // two:first exactMatch to one:2
        // two:second narrowerMatch one:1;

        $schemeOneConceptOne = SkosConcept::whereUri('https://one.scheme/1')->sole();
        
        $schemeOneConceptTwo = SkosConcept::whereUri('https://one.scheme/2')->sole();
        
        $schemeTwoConceptFirst = SkosConcept::whereUri('https://two.scheme/first')->sole();
        
        $schemeTwoConceptSecond = SkosConcept::whereUri('https://two.scheme/second')->sole();

        $this->assertTrue($schemeTwoConceptFirst->mappedConcepts()->first()->is($schemeOneConceptTwo));

        $this->assertTrue($schemeTwoConceptSecond->mappedConcepts()->first()->is($schemeOneConceptOne));
        
        $this->assertTrue($schemeOneConceptTwo->mappedConcepts()->first()->is($schemeTwoConceptFirst));

        $this->assertTrue($schemeOneConceptOne->mappedConcepts()->first()->is($schemeTwoConceptSecond));
    }
}
