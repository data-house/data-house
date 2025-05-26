<?php

namespace App\Livewire\Catalog;

use App\Actions\Catalog\CreateCatalog;
use App\Actions\Review\RequestQuestionReview;
use App\Data\Notifications\ActivitySummaryNotificationData;
use App\Livewire\Concern\InteractWithUser;
use App\Models\Question;
use App\Models\Team;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use LivewireUI\Slideover\SlideoverComponent;

class CreateCatalogSlideover extends SlideoverComponent
{

    use InteractWithUser;


    public $editingForm = [
        'title' => null,
        'description' => null,
    ];


    public function mount()
    {
        abort_unless($this->user, 401);

    }


    public function rules() 
    {
        return [
            'editingForm.title' => 'required|string|min:1|max:255',
            'editingForm.description' => 'nullable|string|min:1|max:6000',
        ];
    }

    public function messages() 
    {
        return [
            'editingForm.title.required' => 'Please select a team.',
            'editingForm.title.min' => 'Please select at least one team.',
            'editingForm.title.exists' => 'Selected team is invalid.',
        ];
    }

    
    public function storeCatalog()
    {
        $this->validate();

        $createCatalog = app()->make(CreateCatalog::class);

        $catalog = $createCatalog(
            title: $this->editingForm['title'],
            description: $this->editingForm['description'],
            user: $this->user,
        );

        // $this->reviewerTeams->each(fn($team) => $createCatalog($this->question, $team));

        $this->dispatch('catalog-created');

        // todo: redirect to catalog using wire:navigate

        $this->redirectRoute('catalogs.show', $catalog, navigate: true);
    }

    
    public function render()
    {
        return view('livewire.catalog.create-catalog-slideover');
    }
}
