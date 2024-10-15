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

    #[Locked]
    public $showAll = false;
    
    #[Locked]
    public $showCreate = false;

    public $waitForSummaryGeneration = false;

    public function mount(Document $document, bool $showAll = false, bool $showCreate = false)
    {
        $this->documentId = $document->getKey();
        $this->showAll = $showAll;
        $this->showCreate = $showCreate;
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
        return $this->document->latestSummary;
    }

    #[Computed()]
    public function languages()
    {
        return $this->document->summaries()->select('language')->distinct()->get()->pluck('language');
    }

    #[Computed()]
    #[On('summary-saved')]
    public function summaries()
    {
        if($this->showAll){
            return $this->document
                ->summaries()
                ->orderBy('created_at', 'DESC')
                ->with('user')
                ->get();
        }

        return $this->languages->map(function($language){
            return $this->document
                ->summaries()
                ->where('language', $language)
                ->orderBy('created_at', 'DESC')
                ->with('user')
                ->first();
        })->filter();
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
