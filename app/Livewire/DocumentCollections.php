<?php

namespace App\Livewire;

use App\Actions\Collection\AddDocument;
use App\Actions\Collection\RemoveDocument;
use App\Models\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class DocumentCollections extends Component
{
    /**
     * @var \App\Models\Document
     */
    public $document;

    public function mount($document)
    {
        $this->document = $document;
    }

    public function add($collectionId)
    {
        $c = Collection::find($collectionId);

        /**
         * @var \App\Actions\Collection\AddDocument
         */
        $add = app()->make(AddDocument::class);

        $add($this->document, $c);
    }
    
    public function remove($collectionId)
    {
        $c = Collection::find($collectionId);

        /**
         * @var \App\Actions\Collection\RemoveDocument
         */
        $remove = app()->make(RemoveDocument::class);

        $remove($this->document, $c);
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


    #[Computed()]
    public function collections()
    {
        return $this->document->collections()
            ->withoutSystem()
            ->visibleBy($this->user)
            ->get();
    }
    
    #[Computed()]
    #[On('collection-created')]
    public function selectableCollections()
    {
        return Collection::query()
            ->withoutSystem()
            ->visibleBy($this->user)
            ->whereNotIn('id', $this->collections->modelKeys())
            ->get();
    }


    public function render()
    {
        return view('livewire.document-collections');
    }
}
