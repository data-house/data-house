<?php

namespace App\Livewire;

use App\Models\Collection;
use App\Models\Flag;
use Laravel\Pennant\Feature;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class CollectionSwitcher extends Component
{
    #[Computed()]
    #[On('collection-created')]
    public function collections()
    {
        $showCollectionsWithTopicGroup = Feature::active(Flag::collectionsTopicGroup());

        return Collection::query()
            ->withoutSystem()
            ->when(!$showCollectionsWithTopicGroup, function($query){
                return $query->whereNull('topic_group');
            })
            ->with('firstNote')
            ->visibleBy(auth()->user())
            ->orderBy('title', 'ASC')->get();
    }

    public function render()
    {
        return view('livewire.collection-switcher');
    }
}
