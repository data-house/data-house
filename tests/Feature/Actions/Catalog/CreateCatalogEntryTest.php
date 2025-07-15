<?php

namespace Tests\Feature\Actions\Catalog;

use App\Actions\Catalog\CreateCatalogEntry;
use App\Models\Catalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateCatalogEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_entry_created_with_only_one_field(): void
    {
        $catalog = Catalog::factory()->withTextField()->create();
        
        $user = $catalog->user;

        $create = app()->make(CreateCatalogEntry::class);

        // I only have one text field

        $valuesToInsert = [
            'field' => (string) $catalog->fields->first()->getKey(),
            'value' => 'The text to store',
        ];

        $entry = $create(
            catalog: $catalog,
            data: [
                'values' => [$valuesToInsert],
            ],
            user: $user,
        );

        $this->assertNotNull($entry);
        $this->assertTrue($entry->user()->is($user));

        $this->assertNull($entry->document);
        $this->assertNull($entry->project);

        $this->assertEquals(1, $entry->catalogValues->count());
        
        $this->assertEquals('The text to store', $entry->catalogValues->first()->value_text);
        $this->assertNull($entry->catalogValues->first()->value_int);
        $this->assertTrue($entry->catalogValues->first()->catalog()->is($catalog));
        $this->assertTrue($entry->catalogValues->first()->catalogField()->is($catalog->fields->first()));
    }
}
