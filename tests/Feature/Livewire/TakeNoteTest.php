<?php

namespace Tests\Feature\Livewire;

use App\Livewire\TakeNote;
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
}
