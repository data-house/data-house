<?php

namespace Tests\Feature\Livewire;

use App\Livewire\StarButton;
use App\Models\Document;
use App\Models\Star;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\TransientToken;
use Livewire\Livewire;
use Tests\TestCase;

class StarButtonTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_star_button_rendered_for_document()
    {
        $user = User::factory()->withPersonalTeam()->create();

        $document = Document::factory()->create();

        Livewire::actingAs($user)
            ->test(StarButton::class, ['model' => $document])
            ->assertStatus(200)
            ->assertSee('Star');
    }
    
    public function test_star_button_rendered_for_starred_document()
    {
        $user = User::factory()->withPersonalTeam()->create();

        $star = Star::factory()
            ->recycle($user)
            ->for(
                Document::factory()->recycle($user), 'starrable'
            )
            ->create();

        $document = $star->starrable;

        Livewire::actingAs($user)
            ->test(StarButton::class, ['model' => $document])
            ->assertStatus(200)
            ->assertSee('Starred')
            ->assertSee('1 user starred this resource');
    }

    
    public function test_star_button_shows_number_of_users_that_starred_a_document()
    {
        $user = User::factory()->withPersonalTeam()->create();

        $stars = Star::factory()
            ->count(3)
            ->for(
                Document::factory()->visibleByAnyUser()->recycle($user), 'starrable'
            )
            ->create();
        
        $document = $stars[0]->starrable;

        Livewire::actingAs($user)
            ->test(StarButton::class, ['model' => $document])
            ->assertStatus(200)
            ->assertSee('3 users starred this resource');
    }


    public function test_starring_document()
    {
        $user = User::factory()->guest()->create()->withAccessToken(new TransientToken);

        $document = Document::factory()->visibleByAnyUser()->create();

        Livewire::actingAs($user)
            ->test(StarButton::class, ['model' => $document])
            ->assertStatus(200)
            ->call('toggle')
            ->assertHasNoErrors();

        $stars = $document->stars()->get();

        $this->assertEquals(1, $stars->count());

        $star = $stars->first();
        
        $this->assertInstanceOf(Star::class, $star);
    }
}
