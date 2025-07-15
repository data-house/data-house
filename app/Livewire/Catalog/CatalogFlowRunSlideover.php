<?php

namespace App\Livewire\Catalog;

use App\Livewire\Concern\InteractWithUser;
use App\Models\CatalogFlow;
use App\Models\Document;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use LivewireUI\Slideover\SlideoverComponent;

class CatalogFlowRunSlideover extends SlideoverComponent
{

    use InteractWithUser;


    #[Locked]
    public $catalogFlowId;
    
    #[Locked]
    public ?int $documentId;


    public function mount($flow, $document = null)
    {
        abort_unless($this->user, 401);

        $catalogFlow = $flow instanceof CatalogFlow ? $flow : CatalogFlow::findOrFail($flow);
        Gate::authorize('view', $catalogFlow);
        $this->catalogFlowId = $catalogFlow->getKey();

        if(blank($document)){
            $this->documentId = null;
            return;
        }
        
        $documentInstance = $document instanceof Document ? $document : Document::findOrFail($document);
        
        Gate::authorize('view', $documentInstance);

        $this->documentId = $documentInstance->getKey();

    }

    #[Computed()]
    public function catalog()
    {
        return $this->flow->catalog;
    }
    
    #[Computed()]
    public function flow()
    {
        return CatalogFlow::find($this->catalogFlowId)
            ->load([
                'catalog',
                'runs' => function($query){
                    $query
                        ->when($this->document, fn($q) => $q->forDocument($this->document))
                        ->orderBy('updated_at', 'desc');
                },
                'runs.user',
                'runs.document',
            ]);
    }
    
    #[Computed()]
    public function document()
    {
        return $this->documentId ? Document::find($this->documentId) : null;
    }
    


    
    public function render()
    {
        return view('livewire.catalog.catalog-flow-run-slideover', [
            'catalog' => $this->catalog,
            'flow' => $this->flow,
            'runs' => $this->flow->runs,
            'document' => $this->document,
        ]);
    }
}
