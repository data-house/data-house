<?php

namespace App\Actions;

use App\Copilot\CopilotSummarizeRequest;
use App\Models\Document;
use App\Models\User;
use App\Models\Visibility;
use App\PdfProcessing\DocumentContent;
use App\PdfProcessing\Facades\Pdf;
use App\PdfProcessing\PdfDriver;
use Exception;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use \Illuminate\Support\Str;

class SuggestDocumentAbstract
{
    /**
     * Suggest a possible abstract for a document
     *
     * @param  \App\Models\Document  $document
     */
    public function __invoke(Document $document, string $language = 'en', array $pageRange = [], int $take = 1): ?string
    {
        $content = null;

        if(is_null($document->properties['pages'] ?? null)){
            throw new InvalidArgumentException("Could not determine the number of pages in the document");
        }

        $isReport = str($document->title)->contains(['Evaluierungsbericht', 'evaluierungsbericht', 'evaluation']);

        $totalPages = (int)$document->properties['pages'];

        // If pageRange is not specified and document is report we assume
        // that each document has an executive summary in
        // english and/or german between page 4 and 9

        list($startPage, $endPage) = count($pageRange) < 2 ? [$isReport ? min(4, $totalPages) : 1, $pageRange[0] ?? ($isReport ? min(9, $totalPages) : 5)] : $pageRange;

        if($endPage < $startPage){
            throw new InvalidArgumentException("End page must be greater or equal to start page [{$startPage}]. Given [{$endPage}].");
        }

        if($endPage-$startPage > 6){
            throw new InvalidArgumentException("The pages to summarize exceed the maximum supported of 6 pages");
        }
        
        if($endPage > $totalPages){
            throw new InvalidArgumentException("The ending page [{$endPage}] is outside of the document [1, {$totalPages}]");
        }

        // Extract pages to summarize 

        $content = $this->getText($document, [$startPage, $endPage])
            ->when($isReport && $language === 'en', function($collection, $value){

                // If it is a report we know that there is a section called summary we can take
                // We don't know in which order it can be, so we try both combination, after the DE summary or before

                if(Str::startsWith($collection->first(), ['ZUSAMMENFASSUNG', 'zusammenfassung'])){
                    return $collection->skipUntil(function ($item) {
                        return Str::startsWith($item, ['SUMMARY', 'summary']);
                    });
                }

                return $collection->takeUntil(function ($item) {
                    return Str::startsWith($item, ['ZUSAMMENFASSUNG', 'zusammenfassung']);
                });
            })
            ->when($isReport && $language === 'de', function($collection, $value){
                
                // If it is a report we know that there is a section called summary we can take
                // We don't know in which order it can be, so we try both combination, after the EN summary or before

                if(Str::startsWith($collection->first(), ['ZUSAMMENFASSUNG', 'zusammenfassung'])){
                    return $collection->takeUntil(function ($item) {
                        return Str::startsWith($item, ['SUMMARY', 'summary']);
                    });
                }

                return $collection->skipUntil(function ($item) {
                    return Str::startsWith($item, ['ZUSAMMENFASSUNG', 'zusammenfassung']);
                });
            })
            ->take($take)
            ->join(PHP_EOL);

        // Based on the structure of the reports we can discard some general data
        // that will make the abstract too long.
        // TODO: make abstract processing more general
        if($isReport && $language === 'en' && Str::contains($content, 'Project description')){
            $content = 'Project description ' . Str::after($content, 'Project description');
        }
        if($isReport && $language === 'de' && Str::contains($content, 'Projektbeschreibung')){
            $content = 'Projektbeschreibung ' . Str::after($content, 'Projektbeschreibung');
        }

        $response = $document->questionableUsing()->summarize(new CopilotSummarizeRequest($document->ulid, $content, $language));

        return $response->text;
    }


    protected function getText(Document $document, array $range): Collection
    {
        try{
            $reference = $document->asReference();

            $content = Pdf::driver(PdfDriver::EXTRACTOR_SERVICE->value)->text($reference);

            list($startPage, $endPage) = $range;

            return $content->collect()
                ->skip($startPage - 1)
                ->take($endPage-$startPage);
        }
        catch(Exception $ex)
        {
            logs()->error("Summary action: Error extracting text from document [{$document->id}]", ['error' => $ex->getMessage()]);
            throw $ex;
        }
    }
}
