<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Note;
use App\Models\Note as ModelsNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\TransientToken;
use Livewire\Livewire;
use Tests\TestCase;

class NoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_note_rendered()
    {
        $user = User::factory()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $note = ModelsNote::factory()->recycle($user)->create([
            'content' => 'Test',
        ]);

        Livewire::actingAs($user)
            ->test(Note::class, [
                'note' => $note,
            ])
            ->assertStatus(200)
            ->assertSee('Test');
    }

    public function test_note_deleted()
    {
        $user = User::factory()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $note = ModelsNote::factory()->recycle($user)->create();

        Livewire::actingAs($user)
            ->test(Note::class, [
                'note' => $note,
            ])
            ->call('remove')
            ->assertStatus(200)
            ->assertDispatched('removed');

        $this->assertNull($note->fresh());
    }
}
