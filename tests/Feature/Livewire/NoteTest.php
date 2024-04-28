<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Note;
use App\Models\Collection;
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
            ->assertSee('Test')
            ->assertSee(today()->toDateString())
            ->assertSee($user->name);
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
            ->assertDispatched('removed')
            ->assertSee('Note removed.');

        $this->assertNull($note->fresh());
    }
    
    public function test_note_deletion_not_authorized()
    {
        $user = User::factory()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $note = ModelsNote::factory()->create();

        Livewire::actingAs($user)
            ->test(Note::class, [
                'note' => $note,
            ])
            ->call('remove')
            ->assertForbidden();

        $this->assertNotNull($note->fresh());
    }

    public function test_entering_edit_mode()
    {
        $user = User::factory()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $note = ModelsNote::factory()->recycle($user)->create([
            'content' => 'Test',
        ]);

        Livewire::actingAs($user)
            ->test(Note::class, [
                'note' => $note,
            ])
            ->call('toggleEditMode')
            ->assertSet('isEditing', true)
            ->assertSet('content', 'Test')
            ->assertSee('Save');
    }
    
    public function test_exit_from_edit_mode()
    {
        $user = User::factory()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $note = ModelsNote::factory()->recycle($user)->create([
            'content' => 'Test',
        ]);

        Livewire::actingAs($user)
            ->test(Note::class, [
                'note' => $note,
                'isEditing' => true,
                'content' => 'Test',
            ])
            ->call('toggleEditMode')
            ->assertSet('isEditing', false)
            ->assertSet('content', null)
            ->assertSee('Edit');
    }

    public function test_note_edited()
    {
        $user = User::factory()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $note = ModelsNote::factory()->recycle($user)->create([
            'content' => 'Test',
        ]);

        Livewire::actingAs($user)
            ->test(Note::class, [
                'note' => $note,
                'isEditing' => true,
                'content' => 'Updated content',
            ])
            ->call('save')
            ->assertStatus(200);

        $this->assertEquals('Updated content', $note->fresh()->content);
    }
    
    public function test_editing_forbidden()
    {
        $user = User::factory()->withPersonalTeam()->create()->withAccessToken(new TransientToken);

        $note = ModelsNote::factory()->create([
            'content' => 'Test',
        ]);

        Livewire::actingAs($user)
            ->test(Note::class, [
                'note' => $note,
                'isEditing' => true,
                'content' => 'Updated content',
            ])
            ->call('save')
            ->assertForbidden();

        $this->assertEquals('Test', $note->fresh()->content);
    }
}
