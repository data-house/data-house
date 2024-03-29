<?php

namespace App\Livewire;

use App\Models\Document;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class DocumentSummariesViewer extends Component
{
    /**
     * @var int
     */
    #[Locked]
    public $documentId;

    public $waitForSummaryGeneration = false;

    public function mount(Document $document)
    {
        $this->documentId = $document->getKey();
    }

    #[Computed()]
    public function document()
    {
        return Document::find($this->documentId);
    }

    #[Computed()]
    public function hasSummary()
    {
        return $this->document()->latestSummary()->exists();
    }

    #[Computed()]
    public function latestSummary()
    {
        return $this->document()->latestSummary;
    }

    #[Computed()]
    public function summaries()
    {
        return $this->document()->summaries;
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


    #[On('generating-summary')] 
    public function updateSummary()
    {
        $this->waitForSummaryGeneration = true;
    }

    public function render()
    {
        if($this->latestSummary()){
            $this->waitForSummaryGeneration = false;
        }

        return view('livewire.document-summaries-viewer');
    }
}
