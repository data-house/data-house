<?php

namespace App\Livewire;

use App\Actions\Collection\CreateCollection as CollectionCreateAction;
use Livewire\Component;

class CreateCollection extends Component
{
    /**
     * Indicates if collection creation is currently being managed.
     *
     * @var bool
     */
    public $currentlyCreatingCollection = false;

    /**
     * The title of the collection
     *
     * @var string
     */
    public $title;
    
    /**
     * The description of the collection
     *
     * @var string
     */
    public $description;

    /**
     * Stop the collection creation.
     *
     * @return void
     */
    public function stopCreatingCollection()
    {
        $this->resetErrorBag();
        $this->title = null;
        $this->currentlyCreatingCollection = false;
    }

    public function createCollection(CollectionCreateAction $create)
    {
        $this->resetErrorBag();

        $createdCollection = $create(
            $this->user,
            [
                'title' => $this->title,
                'description' => $this->description,
            ],
        );

        $this->dispatch('collection-created', collectionId: $createdCollection->getKey()); 

        $this->stopCreatingCollection();
    }

    /**
     * Get the current user of the application.
     *
     * @return mixed
     */
    public function getUserProperty()
    {
        return auth()->user();
    }

    public function render()
    {
        return view('livewire.create-collection');
    }
}
