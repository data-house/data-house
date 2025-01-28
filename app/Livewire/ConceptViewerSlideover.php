<?php

namespace App\Livewire;

use App\Models\SkosConcept;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use LivewireUI\Slideover\SlideoverComponent;

class ConceptViewerSlideover extends SlideoverComponent
{
    /**
     * @var int
     */
    #[Locked]
    public $concept_id;

    public function mount(SkosConcept $concept)
    {
        $this->concept_id = $concept->id;
    }


    #[Computed()]
    public function concept()
    {
        return SkosConcept::find($this->concept_id)
            ->load([
                'related',
                'narrower',
                'broader',
                'conceptScheme',
                'mappingMatches',
                'mappingMatches.conceptScheme',
            ]);
    }
    
    #[Computed()]
    public function vocabulary()
    {
        return $this->concept()->conceptScheme;
    }

    
    public function render()
    {
        return view('livewire.concept-viewer-slideover');
    }
}
