<?php

namespace App\Livewire;

use App\Copilot\CopilotManager;
use Livewire\Component;
use App\Models\Document;
use App\Pipelines\Pipeline;
use Livewire\Attributes\Locked;
use App\Pipelines\PipelineState;
use Livewire\Attributes\Computed;
use App\Jobs\Pipeline\Document\GenerateDocumentSummary;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Modelable;
use PrinsFrank\Standards\Language\LanguageAlpha2;

class DocumentSummaryButton extends Component
{
    /**
     * @var int
     */
    #[Locked]
    public $documentId;

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
    public function summaryLanguages()
    {
        $language = $this->document()->language;

        if(is_null($language)){
            return collect();
        }

        if($language == LanguageAlpha2::English){
            return collect([LanguageAlpha2::English->getNameInLanguage(LanguageAlpha2::English)]);
        }

        return collect([$language->getNameInLanguage(LanguageAlpha2::English), LanguageAlpha2::English->getNameInLanguage(LanguageAlpha2::English)]);
    }
    
    #[Computed()]
    public function pipeline()
    {
        return Document::find($this->documentId)->latestPipelineRun()->whereJob(GenerateDocumentSummary::class)->first();
    }
    
    #[Computed()]
    public function canGenerateSummary()
    {
        return $this->document->language && $this->document->hasTextualContent() && !$this->hasSummary;
    }
    
    #[Computed()]
    public function generatingSummary()
    {
        $pipeline = $this->pipeline();

        return $pipeline && in_array($pipeline->status, [PipelineState::RUNNING, PipelineState::QUEUED, PipelineState::CREATED]);
    }
    
    #[Computed()]
    public function hasSummary()
    {
        return $this->document()->latestSummary()->exists();
    }
    
    #[Computed()]
    public function summaryGenerationFailed()
    {
        $pipeline = $this->pipeline();

        return $pipeline && in_array($pipeline->status, [PipelineState::FAILED, PipelineState::STUCK]);
    }
    
    #[Computed()]
    public function summaryLimit()
    {
        return config('copilot.limits.summaries_per_team');
    }
    
    #[Computed()]
    public function remainingSummaryLimit()
    {
        return CopilotManager::summaryLimitFor($this->user);
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

        if(!$this->canGenerateSummary){
            throw ValidationException::withMessages(['generate_summary' => __('Summary generation is not available if the document contains only images or no selectable text.')]);
        }

        $this->authorize('update', $this->document);

        if($this->generatingSummary()){
            return;
        }

        Pipeline::dispatchOneShotJob($this->document(), GenerateDocumentSummary::class);

        $this->dispatch('generating-summary');

        CopilotManager::trackSummaryHitFor($this->user);
    }


    public function render()
    {
        return view('livewire.document-summary-button');
    }
}
