<?php

namespace Tests\Feature;

use App\Actions\Collection\CreateCollection;
use App\Models\Collection;
use App\Models\CollectionStrategy;
use App\Models\CollectionType;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\TransientToken;
use Tests\TestCase;

class CreateCollectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_collection_can_be_created(): void
    {
        $user = User::factory()->manager()->create()->withAccessToken(new TransientToken);

        $collection = (new CreateCollection)($user, [
            'title' => 'Collection title',
            'visibility' => Visibility::PERSONAL,
            'type' => CollectionType::STATIC,
            'strategy' => CollectionStrategy::LIBRARY,
            'draft' => true,
        ]);

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEquals('Collection title', $collection->title);
        $this->assertEquals(Visibility::PERSONAL, $collection->visibility);
        $this->assertEquals(CollectionType::STATIC, $collection->type);
        $this->assertEquals(CollectionStrategy::LIBRARY, $collection->strategy);
        $this->assertTrue($collection->draft);
        $this->assertEmpty($collection->notes);
    }

    public function test_collection_title_must_be_non_empty(): void
    {
        $user = User::factory()->manager()->create()->withAccessToken(new TransientToken);

        $this->expectException(ValidationException::class);

        $this->expectExceptionMessage('The title field is required');

        $collection = (new CreateCollection)($user, [
            'title' => '',
            'visibility' => Visibility::PERSONAL,
        ]);
    }

    public function test_collection_visibility_is_invalid(): void
    {
        $user = User::factory()->manager()->create()->withAccessToken(new TransientToken);

        $this->expectException(ValidationException::class);

        $this->expectExceptionMessage('The selected visibility is invalid.');

        $collection = (new CreateCollection)($user, [
            'title' => 'A title',
            'visibility' => 2,
            'type' => CollectionType::STATIC,
        ]);
    }


    public function test_team_collection_can_be_created(): void
    {
        $user = User::factory()->manager()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $collection = (new CreateCollection)($user, [
            'title' => 'Collection title',
            'visibility' => Visibility::TEAM,
            'type' => CollectionType::STATIC,
            'strategy' => CollectionStrategy::LIBRARY,
            'draft' => true,
        ]);

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEquals('Collection title', $collection->title);
        $this->assertEquals(Visibility::TEAM, $collection->visibility);
        $this->assertTrue($collection->team->is($user->currentTeam));
        $this->assertEquals(CollectionType::STATIC, $collection->type);
        $this->assertEquals(CollectionStrategy::LIBRARY, $collection->strategy);
        $this->assertTrue($collection->draft);
    }
    
    public function test_team_collection_requires_current_team(): void
    {
        $user = User::factory()->manager()->create()->withAccessToken(new TransientToken);

        $this->expectException(ValidationException::class);

        $this->expectExceptionMessage('Team required, but User doesn\'t have a current team set.');

        $collection = (new CreateCollection)($user, [
            'title' => 'Collection title',
            'visibility' => Visibility::TEAM,
            'type' => CollectionType::STATIC,
            'strategy' => CollectionStrategy::LIBRARY,
            'draft' => true,
        ]);
    }

    public function test_collection_with_note_created(): void
    {
        $user = User::factory()->manager()->create()->withAccessToken(new TransientToken);

        $collection = (new CreateCollection)($user, [
            'title' => 'Collection title',
            'description' => 'Example description',
            'visibility' => Visibility::PERSONAL,
            'type' => CollectionType::STATIC,
            'strategy' => CollectionStrategy::LIBRARY,
            'draft' => true,
        ]);

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEquals('Collection title', $collection->title);
        $this->assertEquals(Visibility::PERSONAL, $collection->visibility);
        $this->assertEquals(CollectionType::STATIC, $collection->type);
        $this->assertEquals(CollectionStrategy::LIBRARY, $collection->strategy);
        $this->assertTrue($collection->draft);
        $this->assertEquals(1, $collection->notes->count());

        $note = $collection->notes->first();

        $this->assertTrue($note->user->is($user));
        $this->assertEquals('Example description', $note->content);
    }
}
