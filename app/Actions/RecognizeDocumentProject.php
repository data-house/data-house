<?php

namespace App\Actions;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Project;

class RecognizeDocumentProject
{
    /**
     * Suggest a possible reference project for the document
     *
     * @param  \App\Models\Document  $document
     */
    public function __invoke(Document $document): ?Project
    {
        $importDocument = $document->importDocument;

        if(! $importDocument){
            return null;
        }

        $str = str($importDocument->source_path);

        if(! $str->contains('[')){
            return null;
        }
        
        $result = $str->matchAll('/\[([a-zA-Z0-9\-\_\s]*)\]/');

        return Project::whereIn('slug', $result)->first();
    }
}
