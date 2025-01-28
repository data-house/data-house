<?php

namespace App\Livewire;

use App\Models\SkosConcept;
use App\Models\SkosConceptScheme;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

class SkosSchemeTree extends Component
{

    #[Locked]
    public int $scheme;


    #[Url(history: true)]
    public ?int $selectedConcept = null;


    public function mount(int $scheme, ?int $selected = null)
    {
        $this->scheme = $scheme;
        $this->selectedConcept = $selected;
    }


    #[Computed()]
    public function vocabulary(): SkosConceptScheme
    {
        return SkosConceptScheme::findOrFail($this->scheme)->load(['topConcepts', 'collections.concepts']);
    }
    
    
    #[Computed()]
    public function vocabularyTopConcepts(): Collection
    {
        return $this->vocabulary()->topConcepts
            ->map(function($c){

                $descendants = $c->descendantsOfConcept()->get();

                return $c
                    ->setRelation('children', $descendants->where('depth', 1))
                    ->setRelation('children_ids', $descendants->pluck('descendant_id'));
            })
            ->sortBy('pref_label', SORT_NATURAL);
    }
    
    
    #[Computed()]
    public function concept(): SkosConcept
    {
        return SkosConcept::findOrFail($this->selectedConcept)
            ->loadCount('documents')
            ->load(['related', 'narrower', 'broader' ]);
    }


    public function render()
    {
        return view('livewire.skos-scheme-tree');
    }
}
