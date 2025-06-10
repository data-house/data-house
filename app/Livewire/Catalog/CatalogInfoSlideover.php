<?php

namespace App\Livewire\Catalog;

use App\Actions\Catalog\CreateCatalog;
use App\Actions\Review\RequestQuestionReview;
use App\Data\Notifications\ActivitySummaryNotificationData;
use App\Livewire\Concern\InteractWithUser;
use App\Models\Catalog;
use App\Models\Question;
use App\Models\Team;
use App\Models\Visibility;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use LivewireUI\Slideover\SlideoverComponent;

class CatalogInfoSlideover extends SlideoverComponent
{

    use InteractWithUser;


    // public $editingForm = [
    //     'title' => null,
    //     'description' => null,
    // ];


    #[Locked]
    public $catalogId;


    public function mount($catalog)
    {
        abort_unless($this->user, 401);

        $catalog = $catalog instanceof Catalog ? $catalog : Catalog::findOrFail($catalog);
        
        $this->user->can('view', $catalog);

        $this->catalogId = $catalog->getKey();

        // $this->editingForm['title'] = $catalog->title;
        // $this->editingForm['description'] = $catalog->description;

    }


    // public function rules() 
    // {
    //     return [
    //         'editingForm.title' => 'required|string|min:1|max:255',
    //         'editingForm.description' => 'nullable|string|min:1|max:6000',
    //     ];
    // }

    // public function messages() 
    // {
    //     return [
    //         'editingForm.title.required' => 'Please provide a title for the catalog.',
    //     ];
    // }

    #[Computed()]
    public function catalog()
    {
        return Catalog::find($this->catalogId)
            ->load(['user', 'team'])
            ->loadCount('entries');
    }

    public function changeVisibility($value)
    {
        $level = match($value) {
            1 => Visibility::PERSONAL,
            2 => Visibility::TEAM,
            3 => Visibility::PROTECTED,
        };

        $catalog = $this->catalog;

        $this->user->can('update', $catalog);

        $catalog->visibility = $level;

        $catalog->save();
    }

    
    public function render()
    {
        return view('livewire.catalog.catalog-info-slideover', [
            'catalog' => $this->catalog,
            'visibility' => $this->catalog->visibility ?? Visibility::PERSONAL,
            'visibility_options' => Visibility::forDocuments(),
            'can_share' => $this->user->can('update', $this->catalog),
        ]);
    }
}
