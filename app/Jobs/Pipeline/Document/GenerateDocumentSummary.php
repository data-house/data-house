<?php

namespace App\Jobs\Pipeline\Document;

use App\Models\Document;
use App\Pipelines\Queue\PipelineJob;
use App\Actions\SuggestDocumentAbstract;
use App\Actions\Summary\SaveSummary;
use App\Models\MimeType;
use InvalidArgumentException;
use PrinsFrank\Standards\Language\LanguageAlpha2;

class GenerateDocumentSummary extends PipelineJob
{
    protected const SUPPORTED_LANGUAGES = [
        LanguageAlpha2::English,
        LanguageAlpha2::German,
        LanguageAlpha2::Spanish_Castilian,
    ];

    /**
     * @var \App\Models\Document
     */
    public $model;

    /**
     * Execute the job.
     */
    public function handle(SuggestDocumentAbstract $suggestAbstract, SaveSummary $saveSummary): void
    {
        if(! $this->model instanceof Document){
            return;
        }

        if(!$this->isSupported($this->model->mime)){
            throw new InvalidArgumentException(__('Summary generation is currently available only for PDF files.'));
        }

        $documentLanguage = $this->model->language ?? LanguageAlpha2::English;

        $summaryLanguages = $documentLanguage != LanguageAlpha2::English && in_array($documentLanguage, self::SUPPORTED_LANGUAGES)
            ? [$documentLanguage, LanguageAlpha2::English]
            : [LanguageAlpha2::English];

        collect($summaryLanguages)->each(function($language) use ($suggestAbstract, $saveSummary): void{

            $abstract = $suggestAbstract($this->model, $language);

            $saveSummary($this->model, $abstract, $language);

        });

        
    }

    protected function isSupported($mime)
    {
        return in_array($mime, [
            MimeType::APPLICATION_PDF->value,
        ]);
    }
}
