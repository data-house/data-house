<?php

namespace App\Livewire;

use App\Actions\Catalog\Flow\ExecuteCatalogFlowOnDocument;
use App\Jobs\ExecuteCatalogFlowJob;
use App\Livewire\Concern\InteractWithUser;
use App\Models\CatalogFlow;
use App\Models\CatalogFlowRun;
use App\Models\Document;
use App\Models\ImportStatus;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DocumentFlowActionButton extends Component
{
    use InteractWithUser;

    public int $documentId;

    public function mount(Document $document)
    {
        abort_unless($this->user, 401);

        Gate::authorize('view', $document);

        $this->documentId = $document->getKey();
    }

    #[Computed()]
    public function document()
    {
        return Document::find($this->documentId);
    }
    
    #[Computed()]
    public function flows()
    {
        return CatalogFlow::query()
            ->whereHas('catalog', function($query){
                $query->visibleTo($this->user);
            })
            ->withCount(['runs' => function($query){
                $query->visibleTo($this->user)->running();   
            }])
            ->get();
    }

    public function triggerFlow($id)
    {
        $flowToTrigger = CatalogFlow::query()
            ->whereHas('catalog', function($query){
                $query->visibleTo($this->user);
            })
            ->whereUuid($id)
            ->sole();

        $this->authorize('view', $flowToTrigger);

        abort_if(blank($this->documentId), 'Document required to execute a flow');
       
        $flowRun = $flowToTrigger->runs()->create([
            'user_id' => $this->user->getKey(),
            'document_id' => $this->document->getKey(),
        ]);
        
        ExecuteCatalogFlowJob::dispatch($flowRun);

        $this->dispatch('openSlideover', 
            component: 'catalog.catalog-flow-run-slideover',
            arguments: [
                'flow' => "{$flowToTrigger->getKey()}",
                'document' => $this->document ? "{$this->document->getKey()}" : null,
            ]
        );
    }

    public function showFlow($id)
    {
        $flowToTrigger = CatalogFlow::query()
            ->whereHas('catalog', function($query){
                $query->visibleTo($this->user);
            })
            ->whereUuid($id)
            ->sole();

        $this->authorize('view', $flowToTrigger);
       
        $this->dispatch('openSlideover', 
            component: 'catalog.catalog-flow-run-slideover',
            arguments: [
                'flow' => "{$flowToTrigger->getKey()}",
                'document' => $this->documentId ? "{$this->document->getKey()}" : null,
            ]
        );
    }
    
    public function render()
    {
        return view('livewire.document-flow-action-button', [
            'flows' => $this->flows,
        ]);
    }
}
