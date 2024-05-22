<?php

namespace App\Livewire;

use App\Actions\RecognizeLanguage;
use App\Actions\Summary\SaveSummary;
use App\Actions\Summary\UpdateSummary;
use App\Models\Document;
use App\Models\DocumentSummary;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use LivewireUI\Slideover\SlideoverComponent;
use PrinsFrank\Standards\Language\LanguageAlpha2;

class SummaryEditor extends SlideoverComponent
{
    /**
     * @var int
     */
    #[Locked]
    public $documentId;
    
    /**
     * @var int|null
     */
    #[Locked]
    public $summaryId;

    #[Locked]
    public $suggestedLanguage =null;

    public $editingForm = [
        'text' => '',
        'language' => null,
    ];

    public function mount(?Document $document = null, ?DocumentSummary $summary = null)
    {
        abort_if(!$document->exists && !$summary->exists, 400, 'Specify one of document or summary');

        $doc = $document->exists ? $document : $summary->document;

        $this->authorize('update', $doc);

        $this->documentId = $doc->getKey();

        $this->summaryId = $summary?->getKey();

        if($summary){
            $this->editingForm = [
                'text' => $summary->text,
                'language' => $summary->language?->value,
            ];

            $this->suggestedLanguage =  $summary->language?->value;
        }
    }


    #[Computed()]
    public function document()
    {
        return Document::find($this->documentId);
    }

    #[Computed()]
    public function summary()
    {
        return $this->summaryId ? DocumentSummary::find($this->summaryId) : null;
    }
    
    #[Computed()]
    public function localizedSuggestedLanguage()
    {
        return $this->suggestedLanguage ? LanguageAlpha2::English::from($this->suggestedLanguage)?->getNameInLanguage(LanguageAlpha2::English) : null;
    }

    #[Computed(persist: true)]
    public function availableLanguages()
    {
        return [
            LanguageAlpha2::English->value => LanguageAlpha2::English->getNameInLanguage(LanguageAlpha2::English),
            LanguageAlpha2::French->value => LanguageAlpha2::French->getNameInLanguage(LanguageAlpha2::English),
            LanguageAlpha2::German->value => LanguageAlpha2::German->getNameInLanguage(LanguageAlpha2::English),
            LanguageAlpha2::Italian->value => LanguageAlpha2::Italian->getNameInLanguage(LanguageAlpha2::English),
            LanguageAlpha2::Russian->value => LanguageAlpha2::Russian->getNameInLanguage(LanguageAlpha2::English),
            LanguageAlpha2::Spanish_Castilian->value => LanguageAlpha2::Spanish_Castilian->getNameInLanguage(LanguageAlpha2::English),
            LanguageAlpha2::Ukrainian->value => LanguageAlpha2::Ukrainian->getNameInLanguage(LanguageAlpha2::English),
        ];
    }

    public function updatedEditingForm($value, $key)
    {
        if($key === 'text' && filled($value) && str($value)->length() > 10 && blank($this->suggestedLanguage) && blank($this->editingForm['language'])){
            
            $this->suggestedLanguage = $this->recognizeLanguage($value)->value;
        }
    }


    public function save(SaveSummary $saveSummary, UpdateSummary $updateSummary)
    {
        $this->resetErrorBag();
        
        $this->authorize('update', $this->document);

        $this->validate([
            'editingForm.text' => ['required', 'max:4000'],
            'editingForm.language' => ['nullable', 'string', 'max:2', new Enum(LanguageAlpha2::class)]
        ], [
            'required' => __('Please provide a summary'),
            'max' => __('The summary should be :max characters, :length in use.', ['length' => str($this->editingForm['text'])->length()]),
        ]);
        
        $language = blank($this->editingForm['language']) ? (filled($this->suggestedLanguage) ? LanguageAlpha2::from($this->suggestedLanguage) : $this->recognizeLanguage($this->editingForm['text'])) : LanguageAlpha2::from($this->editingForm['language']);

        if(blank($this->summaryId)){
            $summary = $saveSummary($this->document, $this->editingForm['text'], $language, auth()->user());
    
            $this->summaryId = $summary->getKey();
        }
        else {
            $summary = $updateSummary($this->summary, $this->editingForm['text'], $language, auth()->user());

            $this->summaryId = $summary->getKey();
        }

        $this->dispatch('summary-saved');

        unset($this->summary); 
        unset($this->document); 
    }

    protected function recognizeLanguage(string $text): LanguageAlpha2
    {
        $recognizeLanguage = app()->make(RecognizeLanguage::class);
        $languages = rescue(fn() => $recognizeLanguage($text));

        if(blank($languages)){
            return LanguageAlpha2::English;
        }

        return LanguageAlpha2::from($languages->first());
    }
    
    public function render()
    {
        return view('livewire.summary-editor');
    }
}
