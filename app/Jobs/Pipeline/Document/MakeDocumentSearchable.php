<?php

namespace App\Jobs\Pipeline\Document;

use App\Models\Document;
use App\PdfProcessing\Facades\Pdf;
use App\Pipelines\Queue\PipelineJob;
use Illuminate\Support\Facades\Storage;

class MakeDocumentSearchable extends PipelineJob
{

    /**
     * @var \App\Models\Document
     */
    public $model;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if(! $this->model instanceof Document){
            return;
        }

        $this->model->searchable();
    }
}
