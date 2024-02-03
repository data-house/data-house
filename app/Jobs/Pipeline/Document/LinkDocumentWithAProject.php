<?php

namespace App\Jobs\Pipeline\Document;

use App\Actions\ClassifyDocumentType;
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
     * Execute the job.
     */
    public function handle(ClassifyDocumentType $classifyType): void
    {
        /*
         * Attempts to connect a document with an existing project
         */

        if(! $this->model instanceof Document){
            return;
        }
    }


    protected function isSupported($mime)
    {
        return in_array($mime, [
            MimeType::APPLICATION_PDF->value,
        ]);
    }
}
