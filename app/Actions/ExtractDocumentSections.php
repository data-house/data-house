<?php

namespace App\Actions;

use App\Models\Document;
use App\PdfProcessing\DocumentContent;
use App\PdfProcessing\Facades\Pdf;
use App\PdfProcessing\PdfDriver;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use PrinsFrank\Standards\Language\LanguageAlpha2;

class ExtractDocumentSections
{
    /**
     * Extract the sections of the document to create a table of contents
     *
     * @param  \App\Models\Document  $document
     */
    public function __invoke(Document $document, LanguageAlpha2 $language = LanguageAlpha2::English): Collection
    {
        try{
            $reference = $document->asReference();

            $content = Cache::rememberForever("pdf-extraction-parse-{$document->getKey()}", function() use ($reference) {
                return Pdf::driver(PdfDriver::PARSE)->text($reference);
            });

            $headings = collect($content->pages())
                ->map->items()
                ->flatten(1)
                ->whereIn('category', ['heading', 'title'])
                ->filter(function($block){

                    $content = str($block['content'])->trim();

                    if($content->length() < 4 || $content->length() > 250){
                        return false;
                    }

                    if($content->isMatch('/^.+[\d+]$/')){
                        return false;
                    }
                    
                    if($content->isMatch('/[\n]+/')){
                        return false;
                    }

                    return !$content->contains(['figure', 'table'], true);
                });

            return $headings->map(function($section, $index){

                return [
                    'title' => $section['content'],
                    'order' => $index,
                    'level' => $section['attributes']['level'] ?? null,
                    'reference' => [
                        'bounding_box' => $section['attributes']['bounding_box'][0] ?? null,
                        'marks' => $section['marks'] ?? null,
                    ],
                ];
            });
        }
        catch(Exception $ex)
        {
            logs()->error("Section action: Error extracting text from document [{$document->id}]", ['error' => $ex->getMessage()]);
            throw $ex;
        }

    }
}
