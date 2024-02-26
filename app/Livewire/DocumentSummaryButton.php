<?php

namespace App\Livewire;

use App\Models\Document;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class DocumentSummaryButton extends Component
{
    /**
     * @var int
     */
    #[Locked]
    public $documentId;

    public $generatingSummary = false;

    public function mount(Document $document)
    {
        $this->documentId = $document->getKey();
    }

    #[Computed()]
    public function document()
    {
        return Document::find($this->documentId);
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


    public function generateSummary()
    {
        $this->resetErrorBag();

        // $createdCollection = $create(
        //     $this->user,
        //     [
        //         'title' => $this->title,
        //     ],
        // );

        // $this->dispatch('collection-created', collectionId: $createdCollection->getKey()); 

        // $this->stopCreatingCollection();

        $this->generatingSummary = true;
    }


    public function render()
    {
        return view('livewire.document-summary-button');
    }
}
