<?php

namespace Tests\Feature;

use App\Actions\Collection\AddDocument;
use App\Actions\Collection\CreateCollection;
use App\Actions\Collection\RemoveDocument;
use App\Models\Collection;
use App\Models\CollectionType;
use App\Models\Document;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RemoveDocumentFromCollectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_removed_from_collection(): void
    {
        $user = User::factory()->manager()->create();

        $collection = Collection::factory()
            ->for($user)
            ->hasAttached(
                Document::factory()->count(3),
            )
            ->create();

        $document = $collection->documents()->first();

        $this->assertEquals(3, $collection->documents()->count());
        
        (new RemoveDocument)($document, $collection);
        
        $this->assertEquals(2, $collection->documents()->count());

        $this->assertNotContains($document->getKey(), $collection->documents->pluck('id'));
    }

}
