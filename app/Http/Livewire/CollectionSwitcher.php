<?php

namespace App\Http\Livewire;

use App\Models\Collection;
use Livewire\Component;

class CollectionSwitcher extends Component
{
    public function render()
    {
        return view('livewire.collection-switcher', [
            'collections' => Collection::query()->withoutSystem()->get(),
        ]);
    }
}
