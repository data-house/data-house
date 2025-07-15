<?php

namespace Tests\Feature\Actions\Catalog;

use App\Actions\Catalog\CreateCatalogEntry;
use App\Actions\Catalog\DeleteCatalogEntry;
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

class DeleteCatalogEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_entry_can_be_trashed(): void
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

        $trash = app()->make(DeleteCatalogEntry::class);

        $entry = $trash(
            entry: $entry,
            user: $user,
        );

        $updatedEntry = $entry->fresh()->load('catalogValues');

        $this->assertNotNull($updatedEntry);
        $this->assertTrue($updatedEntry->trashedBy()->is($user));
        $this->assertTrue($updatedEntry->trashed());

        $this->assertEquals(1, $updatedEntry->catalogValues->count());
        
    }


}
