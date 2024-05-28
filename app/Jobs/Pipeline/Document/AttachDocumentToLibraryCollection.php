<?php

namespace App\Jobs\Pipeline\Document;

use App\Models\Document;
use App\Models\Visibility;
use App\Pipelines\Queue\PipelineJob;
use App\Actions\MatchDocumentCollections;

class AttachDocumentToLibraryCollection extends PipelineJob
{

    /**
     * @var \App\Models\Document
     */
    public $model;

    /**
     * Attempts to connect a document with an existing project.
     */
    public function handle(MatchDocumentCollections $recognize): void
    {
        if(! $this->model instanceof Document){
            return;
        }

        $importDocument = $this->model->importDocument;

        if(! $importDocument){
            return;
        }

        if($this->model->visibility->lowerThan(Visibility::PROTECTED)){
            logs()->info("Skipping automated attachment to collection as document is not visible by all users", ['model' => $this->model->getKey()]);
            return;
        }

        $possibleCollections = $recognize($this->model);

        $alreadyAttachedCollections = collect($this->model->collections()->select('collections.id')->get()->modelKeys());

        $collections = $possibleCollections->filter(fn($c) => !$alreadyAttachedCollections->contains($c->getKey()));

        Document::withoutEvents(function() use ($collections){
            $collections->each(fn($collection) => $collection->documents()->attach($this->model->getKey()));
        });
    }

}
