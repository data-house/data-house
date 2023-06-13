<?php

namespace App\Jobs\Pipeline\Document;

use App\DocumentConversion\Facades\Convert;
use App\DocumentConversion\Format;
use App\Models\Disk;
use App\Models\Document;
use App\PdfProcessing\Facades\Pdf;
use App\Pipelines\Queue\PipelineJob;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Psr7\MimeType;

class ConvertToPdf extends PipelineJob
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

        if(!config('conversion.enable')){
            return ;
        }

        if(!$this->isSupported($this->model->mime)){
            return;
        }

        /**
         * @var \App\DocumentConversion\ConvertedFile
         */
        $convertedFile = Convert::convert($this->model, Format::PDF);

logs()->info("Model", $this->model->toArray());

        $path = $convertedFile->store('', Disk::DOCUMENTS->value);

        $this->model->conversion_disk_name = Disk::DOCUMENTS->value;
        $this->model->conversion_disk_path = $path;
        $this->model->conversion_file_mime = $convertedFile->mimeType();

        logs()->info("converted path", [$path]);

        $this->model->saveQuietly();
    }


    protected function isSupported($mime)
    {
        return in_array($mime, [
            MimeType::fromExtension('docx'),
            MimeType::fromExtension('pptx'),
        ]);
    }
}
