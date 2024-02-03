<?php

namespace App\Jobs\Pipeline\Document;

use App\Actions\ClassifyDocumentType;
use App\Actions\RecognizeDocumentProject;
use App\Models\Document;
use App\Models\MimeType;
use App\PdfProcessing\Facades\Pdf;
use App\Pipelines\Queue\PipelineJob;
use Illuminate\Support\Facades\Storage;

class LinkDocumentWithAProject extends PipelineJob
{

    /**
     * @var \App\Models\Document
     */
    public $model;

    /**
     * Attempts to connect a document with an existing project.
     */
    public function handle(RecognizeDocumentProject $recognize): void
    {
        if(! $this->model instanceof Document){
            return;
        }

        $importDocument = $this->model->importDocument;

        if(! $importDocument){
            return;
        }

        $project = $recognize($this->model);

        $this->model->project()->associate($project);

        $this->model->saveQuietly();
    }

}
