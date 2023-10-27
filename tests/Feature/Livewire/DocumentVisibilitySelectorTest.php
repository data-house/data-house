<?php

namespace Tests\Feature\Livewire;

use App\Livewire\DocumentVisibilitySelector;
use App\Models\Document;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\Rules\Enum;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentVisibilitySelectorTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_current_document_visibility_rendered()
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $document = Document::factory()
            ->recycle($user)
            ->recycle($user->currentTeam)
            ->create([
                'visibility' => Visibility::TEAM
            ]);
            
        Livewire::test(DocumentVisibilitySelector::class, ['document' => $document])
            ->assertStatus(200)
            ->assertSee($user->currentTeam->name)
            ->assertSee(Visibility::TEAM->label())
            ->assertSet('document', $document)
            ->assertSet('selectedVisibility', $document->visibility->value)
            ->assertViewHas('team', $user->currentTeam->name)
            ;
    }
    
    public function test_visibility_options_rendered()
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $document = Document::factory()
            ->recycle($user)
            ->recycle($user->currentTeam)
            ->create([
                'visibility' => Visibility::TEAM
            ]);
            
        Livewire::test(DocumentVisibilitySelector::class, ['document' => $document])
            ->assertStatus(200)
            ->assertSee($user->currentTeam->name)
            ->assertSeeInOrder([
                __('Change Visibility'),
                Visibility::PERSONAL->label(),
                Visibility::TEAM->label(),
                Visibility::PROTECTED->label(),
            ]);
    }
    
    public function test_change_visibility_requires_authentication()
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $document = Document::factory()
            ->recycle($user)
            ->recycle($user->currentTeam)
            ->create([
                'visibility' => Visibility::TEAM
            ]);
            
        Livewire::test(DocumentVisibilitySelector::class, ['document' => $document])
            ->set('selectedVisibility', Visibility::PROTECTED->value)
            ->call('save')
            ->assertForbidden();
    }
    
    public function test_change_visibility_verifies_submitted_value()
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $document = Document::factory()
            ->recycle($user)
            ->recycle($user->currentTeam)
            ->create([
                'visibility' => Visibility::TEAM
            ]);
            
        Livewire::actingAs($user)
            ->test(DocumentVisibilitySelector::class, ['document' => $document])
            ->set('selectedVisibility', 5)
            ->call('save')
            ->assertHasErrors('selectedVisibility');
    }
}
