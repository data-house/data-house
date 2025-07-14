<?php

namespace Tests\Feature\Actions\Catalog;

use App\Actions\Catalog\CreateCatalogEntry;
use App\Actions\Catalog\DeleteCatalogEntry;
use App\Actions\Catalog\PermanentDeleteCatalogEntry;
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

class PermanentDeleteCatalogEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_entry_can_be_permanently_removed(): void
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
            ->create([
                'trashed_at' => now(),
                'trashed_by' => $user->getKey(),
            ])
            ->load('catalogValues');

        $firstValue = $entry->catalogValues->first();

        $forceDelete = app()->make(PermanentDeleteCatalogEntry::class);

        $forceDelete(
            entry: $entry,
            user: $user,
        );

        $this->assertNull($entry->fresh());
        $this->assertNull($firstValue->fresh());
        
    }


}
