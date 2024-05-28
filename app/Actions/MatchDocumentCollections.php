<?php

namespace App\Actions;

use App\Models\Document;
use App\Models\Collection;
use Illuminate\Support\Collection as IlluminateCollection;

class MatchDocumentCollections
{
    /**
     * Suggest possible collections for the document
     *
     * @param  \App\Models\Document  $document
     */
    public function __invoke(Document $document): IlluminateCollection
    {
        $importDocument = $document->importDocument;

        if(! $importDocument){
            return null;
        }

        $str = str($importDocument->source_path);

        if(! $str->contains('[')){
            return null;
        }
        
        $result = $str->matchAll('/\[t:([a-zA-Z0-9\-\_\s]*)\]/');

        return Collection::query()
            ->library()
            ->whereIn('topic_name', $result)
            ->get();
    }
}
