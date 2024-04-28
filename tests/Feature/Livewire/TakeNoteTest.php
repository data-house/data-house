<?php

namespace Tests\Feature\Livewire;

use App\Livewire\TakeNote;
use App\Models\Collection;
use App\Models\Document;
use App\Models\Note;
use App\Models\Star;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\TransientToken;
use Livewire\Livewire;
use Tests\TestCase;

class TakeNoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_note_attached_to_resource()
    {
        $user = User::factory()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $star = Star::factory()
            ->recycle($user)
            ->for(
                Document::factory()->recycle($user), 'starrable'
            )
            ->create();

        Livewire::actingAs($user)
            ->test(TakeNote::class, [
                'resource' => $star
            ])
            ->set('content', 'Example note')
            ->assertSet('content', 'Example note')
            ->call('save')
            ->assertHasNoErrors()
            ->assertStatus(200);

        $note = $star->annotatedByAuthor()->first();

        $this->assertInstanceOf(Note::class, $note);

        $this->assertTrue($note->user->is($user));
        $this->assertTrue($note->noteable->is($star));
        $this->assertEquals('Example note', $note->content);
    }
    
    public function test_missing_note_content()
    {
        $user = User::factory()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $star = Star::factory()
            ->recycle($user)
            ->for(
                Document::factory()->recycle($user), 'starrable'
            )
            ->create();

        Livewire::actingAs($user)
            ->test(TakeNote::class, [
                'resource' => $star
            ])
            ->set('content', null)
            ->call('save')
            ->assertHasErrors(['content' => ['Please add some content to the note before continuing.']]);

        $note = $star->annotatedByAuthor()->first();

        $this->assertNull($note);
    }


    public function test_note_cannot_be_attached_if_user_has_no_rights_on_resource()
    {
        $admin = User::factory()->withPersonalTeam()->manager()->create()->withAccessToken(new TransientToken);
        
        $user = User::factory()->guest()->create()->withAccessToken(new TransientToken);

        $admin->currentTeam->users()->attach(
            $user,
            ['role' => 'guest']
        );

        $collection = Collection::factory()
            ->for($admin)
            ->recycle($admin->currentTeam)
            ->team()
            ->create();

        Livewire::actingAs($user)
            ->test(TakeNote::class, [
                'resource' => $collection
            ])
            ->set('content', 'Example note')
            ->assertSet('content', 'Example note')
            ->call('save')
            ->assertHasNoErrors()
            ->assertForbidden();
    }
}
