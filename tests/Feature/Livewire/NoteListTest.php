<?php

namespace Tests\Feature\Livewire;

use App\Livewire\NoteList;
use App\Models\Collection;
use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class NoteListTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_note_list_renders()
    {
        $collection = Collection::factory()
            ->team()
            ->has(Note::factory()->state(['content' => 'Note content']))
            ->create();
            
        Livewire::test(NoteList::class, [
                'resource' => $collection
            ])
            ->assertStatus(200)
            ->assertSee('Note content');
    }
}
