<?php

namespace App\Jobs\Pipeline\Document;

use App\DocumentConversion\Format;
use App\DocumentThumbnail\Facades\Thumbnail;
use App\Models\Document;
use App\Pipelines\Queue\PipelineJob;
use GuzzleHttp\Psr7\MimeType;

class GenerateThumbnail extends PipelineJob
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

        if(Thumbnail::disabled()){
            return ;
        }

        if(!$this->isSupported($this->model->mime)){
            logs()->info('Document conversion skipped, unsupported mime type', ['mime' => $this->model->mime]);
            return;
        }

        /**
         * @var \App\DocumentThumbnail\FileThumbnail
         */
        $convertedFile = Thumbnail::thumbnail($this->model, Format::PDF);

        $this->model->thumbnail_disk_name = $convertedFile->diskName();
        $this->model->thumbnail_disk_path = $convertedFile->path();

        logs()->info("Thumbnail generation completed path", ['model' => $this->model->getKey(), 'path' => $convertedFile->path()]);

        $this->model->saveQuietly();
    }


    protected function isSupported($mime)
    {
        return in_array($mime, [
            MimeType::fromExtension('png'),
            MimeType::fromExtension('jpg'),
            MimeType::fromExtension('pdf'),
        ]);
    }
}
