<?php

namespace Tests\Feature;

use App\Actions\Collection\AddDocument;
use App\Actions\Collection\CreateCollection;
use App\Models\Collection;
use App\Models\CollectionType;
use App\Models\Document;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AddDocumentCollectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_added_to_collection(): void
    {
        $user = User::factory()->manager()->create();

        $collection = Collection::factory()->for($user)->create();

        $document = Document::factory()->create();

        (new AddDocument)($document, $collection);

        $this->assertTrue($document->collections()->first()->is($collection));
        $this->assertTrue($collection->documents()->first()->is($document));
    }

}
