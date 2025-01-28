<?php

namespace App\View\Components;

use App\Models\SkosConcept;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SkosSchemeTreeItem extends Component
{

    /**
     * Create a new component instance.
     */
    public function __construct(
        public $concept,
        public ?int $selectedConcept = null
        )
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $children = $this->concept->descendantsOfConcept($this->concept->id ?? $this->concept->descendant_id)
            ->get();

        $this->concept
            ->setRelation('children', $children->where('depth', 1)->sortBy('pref_label', SORT_NATURAL))
            ->setRelation('children_ids', $children->pluck('descendant_id'));

        return view('components.skos-scheme-tree-item');
    }
}
