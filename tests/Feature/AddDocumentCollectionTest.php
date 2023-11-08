<?php

namespace Tests\Feature;

use App\Actions\Collection\AddDocument;
use App\Models\Collection;
use App\Models\Document;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Tests\TestCase;

class AddDocumentCollectionTest extends TestCase
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

        (new AddDocument)($document, $collection);
    }

    public function test_add_denied_if_document_not_accessible_by_user(): void
    {
        $user = User::factory()->manager()->create();

        $collection = Collection::factory()->for($user)->create();

        $document = Document::factory()
            ->create();

        $this->expectException(AuthorizationException::class);

        (new AddDocument)($document, $collection, $user);
    }

    public function test_add_denied_if_document_visible_to_team_and_collection_protected(): void
    {
        $user = User::factory()->manager()->create();

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
        $user = User::factory()->manager()->withPersonalTeam()->create();

        $collection = Collection::factory()->for($user)->team()->create();

        $document = Document::factory()
            ->recycle($user->currentTeam)
            ->visibleByTeamMembers()
            ->create();

        (new AddDocument)($document, $collection, $user);

        $this->assertTrue($document->collections()->first()->is($collection));
        $this->assertTrue($collection->documents()->first()->is($document));
    }

}
