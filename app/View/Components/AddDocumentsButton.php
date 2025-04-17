<?php

namespace App\View\Components;

use App\Models\Document;
use App\Models\Import;
use App\Models\Project;
use App\Models\Team;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class AddDocumentsButton extends Component
{


    public ?Project $project;

    public ?Team $currentTeam;

    /**
     * Create a new component instance.
     */
    public function __construct(
        ?Project $project = null
    )
    {
        $this->currentTeam = auth()->user()->currentTeam;
        $this->project = $project?->exists ? $project : $this->currentTeam?->managedProjects()->first();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $directUploadEnabled = config('library.upload.allow_direct_upload');

        $createDocument = Auth::user()->can('create', Document::class);

        $viewImport = Auth::user()->can('viewAny', Import::class);

        $showMenu = $directUploadEnabled || $createDocument || $viewImport;

        /**
         * @var \App\Data\UploadSettings
         */
        $uploadSettings = $this->currentTeam?->settings->upload;

        $uploadLink = $uploadSettings?->uploadLinkUrl;

        if($this->project && $this->project->exists && !is_null($uploadSettings?->limitProjectsTo)){
            $showMenu = $showMenu && (
                empty($uploadSettings->limitProjectsTo)
                || $uploadSettings->limitProjectsTo === '*'
                || str($uploadSettings->limitProjectsTo)->contains($this->project->ulid)
            );
        }

        if($this->project && $this->project->exists && $this->currentTeam){
            $showMenu = $showMenu && $this->project->belongsToTeam($this->currentTeam);
        }
        

        if($uploadLink && $this->project && $this->project->exists && $uploadSettings?->supportProjects){
            $uploadLink .= '?path=' .$this->projectPath($this->project);
        }

        return view('components.add-documents-button', [
            'showMenu' => $showMenu,
            'directUploadEnabled' => $directUploadEnabled,
            'uploadLink' => $uploadLink,
        ]);
    }

    protected function projectPath(Project $project): string
    {
        return urlencode(str("/{$project->title} [{$project->slug}]")->ascii()->toString());
    }
}
