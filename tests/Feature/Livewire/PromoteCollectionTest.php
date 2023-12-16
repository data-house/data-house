<?php

namespace Tests\Feature\Livewire;

use App\Livewire\PromoteCollection;
use App\Models\Collection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\TransientToken;
use Livewire\Livewire;
use Tests\TestCase;

class PromoteCollectionTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_promote_component_loads()
    {
        $user = User::factory()->manager()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $collection = Collection::factory()
            ->for($user)
            ->recycle($user->currentTeam)
            ->team()
            ->create();
            
        Livewire::actingAs($user)
            ->test(PromoteCollection::class, ['collection' => $collection])
            ->assertStatus(200)
            ->assertSet('confirmingPromotion', false)
            ->assertSet('collectionId', $collection->getKey())
            ->assertViewHas('collection_can_be_promoted', true)
            ->assertViewHas('collection_missing_team', false)
            ->assertSee('Promote to Library')
            ;
    }
    
    public function test_promote_component_loads_with_personal_collection()
    {
        $user = User::factory()->manager()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $collection = Collection::factory()
            ->for($user)
            ->create();
            
        Livewire::actingAs($user)
            ->test(PromoteCollection::class, ['collection' => $collection])
            ->assertStatus(200)
            ->assertSet('confirmingPromotion', false)
            ->assertSet('collectionId', $collection->getKey())
            ->assertViewHas('collection_can_be_promoted', true)
            ->assertViewHas('collection_missing_team', true)
            ->assertSee('Promote to Team')
            ;
    }
    
    public function test_promote_confirmation_shown()
    {
        $user = User::factory()->manager()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $collection = Collection::factory()
            ->for($user)
            ->recycle($user->currentTeam)
            ->team()
            ->create();
            
        Livewire::actingAs($user)
            ->test(PromoteCollection::class, ['collection' => $collection])
            ->assertStatus(200)
            ->set('confirmingPromotion', true)
            ->assertSee('Confirm collection promotion')
            ->assertSee('Promote to Library')
            ;
    }
    
    public function test_collection_promoted()
    {
        $user = User::factory()->manager()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $collection = Collection::factory()
            ->for($user)
            ->recycle($user->currentTeam)
            ->team()
            ->create();
            
        Livewire::actingAs($user)
            ->test(PromoteCollection::class, ['collection' => $collection])
            ->assertStatus(200)
            ->set('confirmingPromotion', true)
            ->call('promote')
            ->assertSee('Already promoted')
            ->assertSet('confirmingPromotion', false)
            ;
    }
    
}
