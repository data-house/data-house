<?php

namespace App\Livewire;

use App\Models\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class CollectionSwitcher extends Component
{
    #[Computed()]
    #[On('collection-created')]
    public function collections()
    {
        return Collection::query()->withoutSystem()->with('firstNote')->visibleBy(auth()->user())->orderBy('title', 'ASC')->get();
    }

    public function render()
    {
        return view('livewire.collection-switcher');
    }
}
