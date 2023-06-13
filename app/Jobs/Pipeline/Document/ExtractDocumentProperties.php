<?php

namespace App\Jobs\Pipeline\Document;

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
    public function handle(): void
    {
        if(! $this->model instanceof Document){
            return;
        }

        if(!$this->isSupported($this->model->mime)){
            return;
        }

        $path = Storage::disk($this->model->disk_name)
                ->path($this->model->disk_path);

        $this->model->properties = Pdf::properties($path);

        $this->model->saveQuietly();
    }


    protected function isSupported($mime)
    {
        return in_array($mime, [
            MimeType::APPLICATION_PDF->value,
        ]);
    }
}
