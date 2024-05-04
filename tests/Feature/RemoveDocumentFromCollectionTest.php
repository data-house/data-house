<?php

namespace Tests\Feature;

use App\Actions\Collection\RemoveDocument;
use App\Models\Collection;
use App\Models\Document;
use App\Models\LinkedDocument;
use App\Models\RelationType;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\TransientToken;
use Tests\TestCase;

class RemoveDocumentFromCollectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_required(): void
    {
        $user = User::factory()->manager()->create();

        $collection = Collection::factory()->for($user)->create();

        $document = Document::factory()
            ->visibleByUploader($user)
            ->create();

        $this->expectException(AuthenticationException::class);

        (new RemoveDocument)($document, $collection);
    }

    public function test_remove_denied_if_document_not_accessible_by_user(): void
    {
        $user = User::factory()->manager()->create();

        $collection = Collection::factory()
            ->for($user)
            ->hasAttached(
                Document::factory()->count(3),
            )
            ->create();

        $document = $collection->documents()->first();

        $this->expectException(AuthorizationException::class);
        
        (new RemoveDocument)($document, $collection, $user);
    }

    public function test_document_removed_from_collection(): void
    {
        $user = User::factory()->manager()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $collection = Collection::factory()
            ->for($user)
            ->recycle($user->currentTeam)
            ->hasAttached(
                Document::factory()->visibleByUploader($user)->count(3),
            )
            ->create();

        $document = $collection->documents()->first();

        $relationType = RelationType::factory()->create();

        $linkedDocument = LinkedDocument::findBy($document, $collection);
        
        $linkedDocument->linkTypes()->attach($relationType);
        $linkedDocument->addNote('Test note', $user);

        $this->assertEquals(3, $collection->documents()->count());
        $this->assertNotNull($note = $linkedDocument->fresh()->notes()->first());
        $this->assertNotNull($linkedDocument->fresh()->linkTypes()->first());
        
        (new RemoveDocument)($document, $collection, $user);
        
        $this->assertEquals(2, $collection->documents()->count());

        $this->assertNotContains($document->getKey(), $collection->documents->pluck('id'));

        $this->assertNull($note->fresh());
        $this->assertNotNull($relationType->fresh());
    }

}
