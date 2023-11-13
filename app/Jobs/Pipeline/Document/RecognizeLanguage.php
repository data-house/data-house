<?php

namespace App\Jobs\Pipeline\Document;

use App\Models\Document;
use App\Models\MimeType;
use App\Pipelines\Queue\PipelineJob;
use App\Actions\RecognizeLanguage as ActionsRecognizeLanguage;

class RecognizeLanguage extends PipelineJob
{
    /**
     * @var \App\Models\Document
     */
    public $model;

    /**
     * Execute the job.
     */
    public function handle(ActionsRecognizeLanguage $recognizeLanguage): void
    {
        if(! $this->model instanceof Document){
            return;
        }

        if(!$this->isSupported($this->model->mime)){
            return;
        }

        $this->model->languages = $recognizeLanguage($this->model);

        $this->model->saveQuietly();
    }


    protected function isSupported($mime)
    {
        return in_array($mime, [
            MimeType::APPLICATION_PDF->value,
        ]);
    }
}
