<?php

namespace Tests\Feature\Operations;

use App\Models\Collection;
use App\Models\CollectionStrategy;
use App\Models\CollectionType;
use App\Models\Document;
use App\Models\QuestionFeedback;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BackfillAllDocumentsCollectionTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_all_documents_collection_created(): void
    {
        $documents = Document::factory()->count(2)->create();

        $this->artisan('operations:process 2023_07_24_140452_backfill_all_documents_collection')
            ->assertExitCode(0);

        $collection = Collection::first();

        $this->assertNotNull($collection);

        $this->assertEquals(Visibility::SYSTEM, $collection->visibility);
        $this->assertEquals(CollectionStrategy::LIBRARY, $collection->strategy);
        $this->assertEquals(CollectionType::STATIC, $collection->type); // TODO: in future this collection should be dynamic
        $this->assertFalse($collection->draft);
        $this->assertNull($collection->user);
        $this->assertEquals('All Documents', $collection->title);

        $documentsInCollection = $collection->documents;

        $this->assertEquals(2, $documentsInCollection->count());

        $this->assertEquals($documents->map->getKey()->toArray(), $documentsInCollection->map->getKey()->toArray());
    }
    
    public function test_already_existing_all_documents_collection_not_changed(): void
    {
        $collection = Collection::factory()->create([
            'visibility' => Visibility::SYSTEM,
            'strategy' => CollectionStrategy::LIBRARY,
            'title' => 'All Documents'
        ]);

        $this->artisan('operations:process 2023_07_24_140452_backfill_all_documents_collection')
            ->assertExitCode(0);

        $this->assertEquals(1, Collection::count());
        $this->assertEquals($collection->updated_at, $collection->fresh()->updated_at);
    }
}
