<?php

namespace App\Livewire;

use App\Actions\Collection\AddDocument;
use App\Actions\Collection\RemoveDocument;
use App\Models\Collection;
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


    public function render()
    {
        $user = auth()->user();

        $collections = $this->document->collections()
            ->withoutSystem()
            ->visibleBy($user)
            ->get();
        
        $selectableCollections = Collection::query()
            ->withoutSystem()
            ->visibleBy($user)
            ->whereNotIn('id', $collections->modelKeys())
            ->get();

        return view('livewire.document-collections', [
            'collections' => $collections,
            'selectableCollections' => $selectableCollections,
        ]);
    }
}
