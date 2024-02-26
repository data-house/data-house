<?php

namespace App\Jobs\Pipeline\Document;

use App\Models\Document;
use App\Pipelines\Queue\PipelineJob;
use App\Actions\SuggestDocumentAbstract;
use App\Models\MimeType;
use InvalidArgumentException;
use PrinsFrank\Standards\Language\LanguageAlpha2;

class GenerateDocumentSummary extends PipelineJob
{
    /**
     * @var \App\Models\Document
     */
    public $model;

    /**
     * Execute the job.
     */
    public function handle(SuggestDocumentAbstract $suggestAbstract): void
    {
        if(! $this->model instanceof Document){
            return;
        }

        if(!$this->isSupported($this->model->mime)){
            throw new InvalidArgumentException(__('Summary generation is currently available only for PDF files.'));
        }

        $language = $this->model->language ?? LanguageAlpha2::English;

        // TODO: handle document not in English

        $abstract = $suggestAbstract($this->model, $language);
        
        $this->model->summaries()->create([
            'text' => $abstract,
            'ai_generated' => true,
            'language' => $language,
        ]);

        // TODO: check if this triggers the model_saved pipeline
        
    }

    protected function isSupported($mime)
    {
        return in_array($mime, [
            MimeType::APPLICATION_PDF->value,
        ]);
    }
}
