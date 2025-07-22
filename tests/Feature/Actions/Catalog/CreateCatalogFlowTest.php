<?php

namespace Tests\Feature\Actions\Catalog;

use App\Actions\Catalog\CreateCatalogEntry;
use App\Actions\Catalog\CreateCatalogFlow;
use App\Catalog\Flow\FlowSourceEntity;
use App\Catalog\Flow\FlowTrigger;
use App\Data\Catalog\Flows\StructuredExtractionConfigurationData;
use App\Models\Catalog;
use App\Models\CatalogFlow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateCatalogFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_flow_created(): void
    {
        $catalog = Catalog::factory()->withTextField()->create();

        $targetField = $catalog->fields()->first();
        
        $user = $catalog->user;

        $create = app()->make(CreateCatalogFlow::class);

        $configuration = new StructuredExtractionConfigurationData(
            schema: [
                "type" => "json_schema",
                "json_schema" => [
                    "name" => "recommendations",
                    "schema" => [
                        "type" => "object",
                        "properties" => [
                            "recommendations" => [
                            "type" => "array",
                            "items" => [
                                "type" => "object",
                                "properties" => [
                                "recommendation" => [
                                    "description" => "A recommendation given by the project written as in the given text (do not ri-elaborate this).",
                                    "title" => "Recommendation ",
                                    "type" => "string"
                                ],
                                ],
                                "required" => [
                                "recommendation",
                                ]
                            ],
                            "additionalItems" => false
                            ]
                        ],
                        "required" => [
                            "recommendations"
                        ]
                    ]
                ]
            ],
            attributes_to_field: [
                $targetField->uuid => 'recommendations'
            ],
            instructions: 'other instructions',
            document_sections: ["executive summary", "lessons learnt and recommendations"],
        );

        $flow = $create(
            catalog: $catalog,
            title: "Test structured extraction flow",
            configuration: $configuration,  
            description: "Test",
            user: $user,
        );

        $freshFlow = CatalogFlow::find($flow->getKey());

        $this->assertNotNull($flow);
        $this->assertTrue($flow->user()->is($user));
        $this->assertTrue($flow->catalog()->is($catalog));

        $this->assertEquals(FlowTrigger::MANUAL, $flow->trigger);
        $this->assertEquals(FlowSourceEntity::DOCUMENT, $flow->target_entity);
        $this->assertEquals("Test structured extraction flow", $flow->title);
        $this->assertEquals("Test", $flow->description);
        
        $this->assertInstanceOf(StructuredExtractionConfigurationData::class, $flow->configuration);
        $this->assertInstanceOf(StructuredExtractionConfigurationData::class, $freshFlow->configuration);

        $schema = $flow->configuration->schema;

        $this->assertArrayHasKey('json_schema', $schema);
        $this->assertArrayHasKey('recommendations', $schema['json_schema']['schema']['properties']);
        $this->assertEquals([$targetField->uuid => 'recommendations'], $flow->configuration->attributes_to_field);
        $this->assertEquals('other instructions', $flow->configuration->instructions);
    }
}
