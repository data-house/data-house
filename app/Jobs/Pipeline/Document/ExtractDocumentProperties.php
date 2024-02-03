<?php

namespace App\Jobs\Pipeline\Document;

use App\Actions\ClassifyDocumentType;
use App\Models\Document;
use App\Models\MimeType;
use App\PdfProcessing\Facades\Pdf;
use App\Pipelines\Queue\PipelineJob;
use Illuminate\Support\Facades\Storage;

class ExtractDocumentProperties extends PipelineJob
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
        if(! $this->model instanceof Document){
            return;
        }

        if(!$this->isSupported($this->model->mime)){
            return;
        }

        $properties = Pdf::properties($this->model->asReference())->jsonSerialize();

        $this->model->type = $classifyType($this->model);

        $this->model->properties =  $this->model->properties->collect()->merge($properties);

        $this->model->saveQuietly();
    }


    protected function isSupported($mime)
    {
        return in_array($mime, [
            MimeType::APPLICATION_PDF->value,
        ]);
    }
}
