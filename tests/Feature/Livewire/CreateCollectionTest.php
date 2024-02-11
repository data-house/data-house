<?php

namespace Tests\Feature\Livewire;

use App\Livewire\CreateCollection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\TransientToken;
use Livewire\Livewire;
use Tests\TestCase;

class CreateCollectionTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_new_button_rendered()
    {
        $user = User::factory()->manager()->create()->withAccessToken(new TransientToken);

        Livewire::actingAs($user)
            ->test(CreateCollection::class)
            ->assertStatus(200)
            ->assertSee('New');
    }
    
    public function test_nothing_rendered_when_user_cannot_create_collection()
    {
        $user = User::factory()->guest()->create();

        Livewire::actingAs($user)
            ->test(CreateCollection::class)
            ->assertStatus(200)
            ->assertDontSee('New');
    }


    public function test_creation_action()
    {
        $user = User::factory()->manager()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $this->withoutExceptionHandling();

        $component = Livewire::actingAs($user)
            ->test(CreateCollection::class)
            ->assertStatus(200)
            ->set('currentlyCreatingCollection', true)
            ->set('title', 'A Collection')
            ->call('createCollection')
            ->assertHasNoErrors('title')
            ->assertDispatched('collection-created')
            ->assertSet('currentlyCreatingCollection', false)
            ->assertSet('title', null);
    }
}
