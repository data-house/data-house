<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Str;

class CollectionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_creation_form_requires_login(): void
    {
        $response = $this->get('/collections/create');

        $response->assertRedirectToRoute('login');
    }

    public function test_creation_form_not_authorized(): void
    {
        $user = User::factory()->guest()->create();

        $response = $this->actingAs($user)
            ->get('/collections/create');

        $response->assertForbidden();
    }

    public function test_creation_form_loads(): void
    {
        $user = User::factory()->withPersonalTeam()->manager()->create();

        $response = $this->actingAs($user)
            ->get('/collections/create');

        $response->assertOk();

        $response->assertViewIs('collection.create');
        
        $response->assertSee('Collection title');
    }

    public function test_collection_can_be_created(): void
    {
        $user = User::factory()->withPersonalTeam()->manager()->create();

        $response = $this->actingAs($user)
            ->post('/collections', [
                'title' => 'Test collection',
            ]);

        $collection = Collection::first();

        $response->assertRedirectToRoute('collections.show', $collection);

        $response->assertSessionHas('flash.banner', 'Collection created.');
        
        $this->assertEquals('Test collection', $collection->title);
        $this->assertTrue($collection->user->is($user));
        $this->assertTrue($collection->team->is($user->currentTeam));
        $this->assertEquals(Visibility::TEAM, $collection->visibility);
    }

    public function test_title_required_when_creating_collection(): void
    {
        $user = User::factory()->withPersonalTeam()->manager()->create();

        $response = $this->actingAs($user)
            ->from(route('collections.create'))
            ->post('/collections');


        $response->assertRedirectToRoute('collections.create');

        $response->assertSessionHasErrors('title');
        
        $this->assertNull(Collection::first());
    }
    
    public function test_title_length_verified_when_creating_collection(): void
    {
        $user = User::factory()->withPersonalTeam()->manager()->create();

        $response = $this->actingAs($user)
            ->from(route('collections.create'))
            ->post('/collections', [
                'title' => Str::random(256),
            ]);


        $response->assertRedirectToRoute('collections.create');

        $response->assertSessionHasErrors('title');
        
        $this->assertNull(Collection::first());
    }

    public function test_collection_viewable(): void
    {
        $user = User::factory()->withPersonalTeam()->guest()->create();

        $collection = Collection::factory()
            ->for($user)
            ->create(['visibility' => Visibility::PERSONAL]);

        $response = $this->actingAs($user)
            ->get(route('collections.show', $collection));

        $response->assertSuccessful();
        
        $response->assertViewIs('collection.show');

        $response->assertViewHas('collection', $collection);

        $response->assertViewHas('documents', new EloquentCollection());
        
        $response->assertViewHas('questions', new EloquentCollection());

        $response->assertSee($collection->title);

        $response->assertSee('Only to me (personal)');
    }

    public function test_team_collection_viewable(): void
    {
        $user = User::factory()->withPersonalTeam()->guest()->create();

        $collection = Collection::factory()
            ->for($user)
            ->recycle($user->currentTeam)
            ->team()
            ->create();

        $response = $this->actingAs($user)
            ->get(route('collections.show', $collection));

        $response->assertSuccessful();
        
        $response->assertViewIs('collection.show');

        $response->assertViewHas('collection', $collection);

        $response->assertViewHas('documents', new EloquentCollection());

        $response->assertViewHas('questions', new EloquentCollection());

        $response->assertSee($collection->title);

        $response->assertSee('Team members');
    }

    public function test_library_collection_viewable(): void
    {
        $user = User::factory()->withPersonalTeam()->guest()->create();

        $collection = Collection::factory()
            ->for($user)
            ->recycle($user->currentTeam)
            ->library()
            ->create();

        $response = $this->actingAs($user)
            ->get(route('collections.show', $collection));

        $response->assertSuccessful();
        
        $response->assertViewIs('collection.show');

        $response->assertViewHas('collection', $collection);

        $response->assertViewHas('documents', new EloquentCollection());

        $response->assertViewHas('questions', new EloquentCollection());

        $response->assertSee($collection->title);

        $response->assertSee('Authenticated users');
    }
    
    public function test_collection_view_requires_login(): void
    {
        $user = User::factory()->withPersonalTeam()->guest()->create();

        $collection = Collection::factory()
            ->for($user)
            ->create(['visibility' => Visibility::PERSONAL]);

        $response = $this->get(route('collections.show', $collection));

        $response->assertRedirectToRoute('login');
    }



    public function test_editing_form_requires_login(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->get(route('collections.edit', $collection));

        $response->assertRedirectToRoute('login');
    }

    public function test_editing_form_not_authorized(): void
    {
        $user = User::factory()->guest()->create();

        $collection = Collection::factory()
            ->for($user)
            ->create();

        $response = $this->actingAs($user)
            ->get(route('collections.edit', $collection));

        $response->assertForbidden();
    }

    public function test_editing_form_loads(): void
    {
        $user = User::factory()->withPersonalTeam()->manager()->create();

        $collection = Collection::factory()
            ->for($user)
            ->recycle($user->currentTeam)
            ->team()
            ->create();

        $response = $this->actingAs($user)
            ->get(route('collections.edit', $collection));

        $response->assertOk();

        $response->assertViewIs('collection.edit');
        
        $response->assertSee('Save');
    }

    public function test_collection_can_be_updated(): void
    {
        $user = User::factory()->withPersonalTeam()->manager()->create();

        $collection = Collection::factory()
            ->for($user)
            ->recycle($user->currentTeam)
            ->team()
            ->create();

        $response = $this->actingAs($user)
            ->put(route('collections.update', $collection), [
                'title' => 'Test collection',
            ]);

        $updatedCollection = $collection->fresh();

        $response->assertRedirectToRoute('collections.show', $updatedCollection);

        $response->assertSessionHas('flash.banner', 'Collection updated.');
        
        $this->assertEquals('Test collection', $updatedCollection->title);
        $this->assertTrue($updatedCollection->user->is($user));
        $this->assertTrue($updatedCollection->team->is($user->currentTeam));
        $this->assertEquals(Visibility::TEAM, $updatedCollection->visibility);
    }

    public function test_title_required_when_updating_collection(): void
    {
        $user = User::factory()->withPersonalTeam()->manager()->create();

        $collection = Collection::factory()
            ->for($user)
            ->recycle($user->currentTeam)
            ->team()
            ->create();

        $response = $this->actingAs($user)
            ->from(route('collections.edit', $collection))
            ->put(route('collections.update', $collection));


        $response->assertRedirectToRoute('collections.edit', $collection);

        $response->assertSessionHasErrors('title');
    }
    
    public function test_title_length_verified_when_updating_collection(): void
    {
        $user = User::factory()->withPersonalTeam()->manager()->create();

        $collection = Collection::factory()
            ->for($user)
            ->recycle($user->currentTeam)
            ->team()
            ->create();

        $response = $this->actingAs($user)
            ->from(route('collections.edit', $collection))
            ->put(route('collections.update', $collection), [
                'title' => Str::random(256),
            ]);

        $response->assertRedirectToRoute('collections.edit', $collection);

        $response->assertSessionHasErrors('title');
        
        $this->assertEquals($collection->title, $collection->fresh()->title);
    }
}
