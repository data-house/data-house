<?php

namespace App\Livewire;

use App\Actions\Collection\PromoteCollection as ActionPromoteCollection;
use App\Models\Collection;
use App\Models\Visibility;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PromoteCollection extends Component
{
    /**
     * @var int
     */
    #[Locked]
    public $collectionId;


    public $confirmingPromotion = false;


    #[Computed()]
    public function collection()
    {
        return Collection::find($this->collectionId);
    }


    public function mount(Collection $collection)
    {
        $this->collectionId = $collection->getKey();
    }

    public function promote()
    {
        /**
         * @var \App\Actions\Collection\PromoteCollection
         */
        $promote = app()->make(ActionPromoteCollection::class);

        try {
            $promote(auth()->user(), $this->collection, is_null($this->collection->team_id) ? Visibility::TEAM : Visibility::PROTECTED);
        } catch (AuthorizationException|InvalidArgumentException $th) {
            throw ValidationException::withMessages(['promote' => $th->getMessage()]);
        }

        $this->confirmingPromotion = false;
    }
    


    public function render()
    {
        return view('livewire.promote-collection', [
            'collection_can_be_promoted' => $this->collection->visibility->lowerThan(Visibility::PROTECTED),
            'collection_missing_team' => is_null($this->collection->team_id),
        ]);
    }
}
