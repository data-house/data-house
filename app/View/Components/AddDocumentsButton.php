<?php

namespace App\View\Components;

use App\Models\Document;
use App\Models\Import;
use App\Models\Project;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class AddDocumentsButton extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public ?Project $project = null
    )
    {
        //
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
        $uploadSettings = auth()->user()->currentTeam?->settings->upload;

        $uploadLink = $uploadSettings?->uploadLinkUrl;

        if($uploadLink && $this->project && $this->project->exists && $uploadSettings?->supportProjects){
            $uploadLink .= '?path=' .urlencode(str("/{$this->project->title} [{$this->project->slug}]")->ascii()->toString());
        }

        return view('components.add-documents-button', [
            'showMenu' => $showMenu,
            'directUploadEnabled' => $directUploadEnabled,
            'uploadLink' => $uploadLink,
        ]);
    }
}
