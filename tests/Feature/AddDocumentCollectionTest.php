<?php

namespace Tests\Feature;

use App\Actions\Collection\AddDocument;
use App\Models\Collection;
use App\Models\Document;
use App\Models\RelationType;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Laravel\Sanctum\TransientToken;
use Tests\TestCase;

class AddDocumentCollectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_required(): void
    {
        $user = User::factory()->manager()->create()->withAccessToken(new TransientToken);

        $collection = Collection::factory()->for($user)->create();

        $document = Document::factory()
            ->visibleByUploader($user)
            ->create();

        $this->expectException(AuthenticationException::class);

        (new AddDocument)($document, $collection);
    }

    public function test_add_denied_if_document_not_accessible_by_user(): void
    {
        $user = User::factory()->manager()->create()->withAccessToken(new TransientToken);

        $collection = Collection::factory()->for($user)->create();

        $document = Document::factory()
            ->create();

        $this->expectException(AuthorizationException::class);

        (new AddDocument)($document, $collection, $user);
    }

    public function test_add_denied_if_document_visible_to_team_and_collection_protected(): void
    {
        $user = User::factory()->manager()->create()->withAccessToken(new TransientToken);

        $collection = Collection::factory()
            ->for($user)
            ->create(['visibility' => Visibility::PROTECTED]);

        $document = Document::factory()
            ->visibleByUploader($user)
            ->create();

        $this->expectException(ValidationException::class);
        
        $this->expectExceptionMessage('Team document cannot be added to a collection visible by all authenticated users.');

        (new AddDocument)($document, $collection, $user);

        $this->assertNull($document->collections()->first());
        $this->assertNull($collection->documents()->first());
    }

    public function test_document_added_to_collection(): void
    {
        $relationType = RelationType::factory()->create();

        $user = User::factory()->manager()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $collection = Collection::factory()->for($user)->recycle($user->currentTeam)->team()->create(['title' => 'Approach: Private Sector']);

        $document = Document::factory()
            ->recycle($user->currentTeam)
            ->visibleByTeamMembers()
            ->create();

        $linkedDocument = (new AddDocument)($document, $collection, $user);

        $linkedDocument->linkTypes()->attach($relationType);

        $this->assertTrue($linkedDocument->user->is($user));
        $this->assertTrue($document->collections()->first()->is($collection));
        $this->assertTrue($collection->documents()->first()->is($document));

        $document = $collection->documents()->with('pivot.user', 'pivot.collection', 'pivot.linkTypes')->first();

        $this->assertTrue($document->pivot->is($linkedDocument));
        $this->assertTrue($document->pivot->linkTypes()->first()->is($relationType));
        $this->assertTrue($document->pivot->collection->is($collection));
        $this->assertTrue($document->pivot->user->is($user));
    }

    public function test_linked_document_have_notes(): void
    {
        $user = User::factory()->manager()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $collection = Collection::factory()->for($user)->recycle($user->currentTeam)->team()->create(['title' => 'Approach: Private Sector']);

        $document = Document::factory()
            ->recycle($user->currentTeam)
            ->visibleByTeamMembers()
            ->create();

        $linkedDocument = (new AddDocument)($document, $collection, $user);

        $linkedDocument->addNote('Note content', $user);

        $notes = $linkedDocument->fresh()->notes;

        $this->assertCount(1, $notes);
        $this->assertEquals('Note content', $notes->first()->content);
    }

}
