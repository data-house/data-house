<?php

namespace Tests\Feature\Actions\Catalog;

use App\Actions\Catalog\CreateCatalogEntry;
use App\Actions\Catalog\UpdateCatalogEntry;
use App\CatalogFieldType;
use App\Models\Catalog;
use App\Models\CatalogEntry;
use App\Models\CatalogField;
use App\Models\CatalogValue;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateCatalogEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_single_field_value_can_be_updated(): void
    {
        $catalog = Catalog::factory()->withTextField()->create();

        $user = $catalog->user;

        $document = Document::factory()->visibleByUploader($user)->create();

        $field = $catalog->fields->first();

        $entry = CatalogEntry::factory()
            ->recycle($user)
            ->recycle($catalog)
            ->has(CatalogValue::factory()
                ->recycle($user)
                ->recycle($catalog)
                ->recycle($field)
            )
            ->create()
            ->load('catalogValues');       

        $update = app()->make(UpdateCatalogEntry::class);

        $valuesToInsert = [
            'field' => (string) $catalog->fields->first()->getKey(),
            'value' => 'Changed text',
        ];

        $entry = $update(
            entry: $entry,
            catalog: $catalog,
            data: [
                'document_id' => $document->getKey(),
                'values' => [$valuesToInsert],
            ],
            user: $user,
        );

        $updatedEntry = $entry->fresh()->load('catalogValues');

        $this->assertNotNull($updatedEntry);
        $this->assertTrue($updatedEntry->user()->is($user));
        $this->assertTrue($updatedEntry->lastUpdatedBy()->is($user));

        $this->assertTrue($updatedEntry->document()->is($document));
        $this->assertNull($updatedEntry->project);

        $this->assertEquals(1, $updatedEntry->catalogValues->count());
        
        $this->assertEquals('Changed text', $updatedEntry->catalogValues->first()->value_text);
        $this->assertNull($updatedEntry->catalogValues->first()->value_int);
        $this->assertTrue($updatedEntry->catalogValues->first()->catalog()->is($catalog));
        $this->assertTrue($updatedEntry->catalogValues->first()->lastUpdatedBy()->is($user));
        $this->assertTrue($updatedEntry->catalogValues->first()->catalogField()->is($catalog->fields->first()));
    }

    public function test_values_inserted_for_missing_fields(): void
    {
        $catalog = Catalog::factory()
            ->has(CatalogField::factory()->state(['data_type' => CatalogFieldType::TEXT])->count(2), 'fields')
            ->create();

        $user = $catalog->user;

        $firstField = $catalog->fields->first();
        $lastField = $catalog->fields->last();

        $entry = CatalogEntry::factory()
            ->recycle($user)
            ->recycle($catalog)
            ->has(CatalogValue::factory()
                ->recycle($user)
                ->recycle($catalog)
                ->recycle($firstField)
            )
            ->create()
            ->load('catalogValues');       

        $update = app()->make(UpdateCatalogEntry::class);

        $valuesToInsert = [
            [
                'field' => (string) $firstField->getKey(),
                'value' => 'Changed text',
            ],
            [
                'field' => (string) $lastField->getKey(),
                'value' => 'New text',
            ],
        ];

        $entry = $update(
            entry: $entry,
            catalog: $catalog,
            data: [
                'values' => $valuesToInsert,
            ],
            user: $user,
        );

        $updatedEntry = $entry->fresh()->load('catalogValues');

        $this->assertNotNull($updatedEntry);
        $this->assertTrue($updatedEntry->user()->is($user));
        $this->assertTrue($updatedEntry->lastUpdatedBy()->is($user));

        $this->assertNull($updatedEntry->document);
        $this->assertNull($updatedEntry->project);

        $this->assertEquals(2, $updatedEntry->catalogValues->count());

        $firstValue = $updatedEntry->catalogValues->first();

        $lastValue =  $updatedEntry->catalogValues->last();
        
        $this->assertEquals('Changed text', $firstValue->value_text);
        $this->assertNull($firstValue->value_int);
        $this->assertTrue($firstValue->lastUpdatedBy()->is($user));
        $this->assertTrue($firstValue->catalog()->is($catalog));
        $this->assertTrue($firstValue->catalogField()->is($firstField));
        
        $this->assertEquals('New text', $lastValue->value_text);
        $this->assertNull($lastValue->value_int);
        $this->assertTrue($lastValue->user()->is($user));
        $this->assertTrue($lastValue->catalog()->is($catalog));
        $this->assertTrue($lastValue->catalogField()->is($lastField));
    }
}
