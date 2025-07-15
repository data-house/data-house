<?php

namespace App\Livewire;

use App\Actions\Collection\AddDocument;
use App\Actions\Collection\RemoveDocument;
use App\Livewire\Concern\InteractWithUser;
use App\Models\Collection;
use App\Models\Document;
use App\Models\Flag;
use App\Models\Project;
use Illuminate\Support\Facades\Validator;
use Laravel\Pennant\Feature;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class DocumentProject extends Component
{
    use InteractWithUser;

    /**
     * @var \App\Models\Document
     */
    #[Locked]
    public $documentId;


    public bool $projectSelection = false;

    public ?string $search = null;

    public function mount($document)
    {
        $this->documentId = $document instanceof Document ? $document->getKey() : $document;
    }


    #[Computed()]
    public function project()
    {
        return $this->document->project;
    }
    
    #[Computed()]
    public function document()
    {
        return Document::findOrFail($this->documentId);
    }
    
    #[Computed()]
    public function selectableProjects()
    {
        if(filled($this->search)){
            return Project::advancedSearch($this->search)->paginate(12);
        }

        return Project::query()
            ->latest()
            ->paginate(12);
    }


    public function unlinkProject()
    {
        $this->authorize('update', $this->document);


        $this->document->project_id = null;

        $this->document->save();

        unset($this->project);
        unset($this->document);
    }
    
    public function linkProject($prj)
    {
        $this->authorize('update', $this->document);

        $validated = Validator::make(
            ['project' => $prj],
            ['project' => 'required|integer|exists:projects,id'],
            [
                'required' => 'The :attribute field is required.',
                'exists' => 'Looks like the project is not valid.',
            ],
         )->validate();


        $this->document->project_id = $validated['project'];

        $this->document->save();

        unset($this->project);
        unset($this->document);
    }


    public function render()
    {
        return view('livewire.document-project', [
            'project' => $this->project,
            'selectableProjects' => $this->projectSelection ? $this->selectableProjects : collect(),
        ]);
    }
}
