<?php

namespace App\Actions;

use App\Copilot\CopilotSummarizeRequest;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\User;
use App\Models\Visibility;
use App\PdfProcessing\DocumentContent;
use App\PdfProcessing\Facades\Pdf;
use App\PdfProcessing\PdfDriver;
use Exception;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use \Illuminate\Support\Str;
use PrinsFrank\Standards\Language\LanguageAlpha2;

class SuggestDocumentAbstract
{
    /**
     * Suggest a possible abstract for a document
     *
     * @param  \App\Models\Document  $document
     */
    public function __invoke(Document $document, LanguageAlpha2 $language = LanguageAlpha2::English, array $pageRange = []): ?string
    {
        $content = null;

        // TODO: switch to generate summary for all text by default and consider only pages if explicitly asked

        if(is_null($document->properties['pages'] ?? null)){
            throw new InvalidArgumentException("Could not determine the number of pages in the document");
        }

        $totalPages = (int)$document->properties['pages'];

        list($startPage, $endPage) = count($pageRange) < 2 ? [1, $pageRange[0] ?? $totalPages] : $pageRange;

        if($endPage < $startPage){
            throw new InvalidArgumentException("End page must be greater or equal to start page [{$startPage}]. Given [{$endPage}].");
        }
        
        if($endPage > $totalPages){
            throw new InvalidArgumentException("The ending page [{$endPage}] is outside of the document [1, {$totalPages}]");
        }

        $content = $this->getText($document, [$startPage, $endPage])
            ->join(PHP_EOL);

        $response = $document->questionableUsing()->summarize(new CopilotSummarizeRequest($document->getCopilotKey(), $content, $language));

        return $response->text;
    }


    protected function getText(Document $document, array $range): Collection
    {
        try{
            $reference = $document->asReference();

            $content = Pdf::driver(PdfDriver::PARSE)->text($reference);

            list($startPage, $endPage) = $range;

            return collect($content->pages())
                ->skip($startPage - 1)
                ->take(max(1, $endPage-$startPage));
        }
        catch(Exception $ex)
        {
            logs()->error("Summary action: Error extracting text from document [{$document->id}]", ['error' => $ex->getMessage()]);
            throw $ex;
        }
    }
}
