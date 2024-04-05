<?php

namespace App\Livewire\Concepts;

use App\Models\ConceptCollection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ConceptCollectionListingComponent extends Component
{


    #[Computed()]
    public function collections()
    {
        return ConceptCollection::query()->latest()->get();
    }


    public function render()
    {
        return view('livewire.concepts.concept-collection-listing-component');
    }
}
