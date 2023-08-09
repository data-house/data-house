<?php

namespace App\Actions;

use App\Models\Document;
use App\Models\DocumentType;

class ClassifyDocumentType
{
    /**
     * Suggest a possible document classification according to the document types hierarchy
     *
     * @param  \App\Models\Document  $document
     */
    public function __invoke(Document $document): ?DocumentType
    {
        $isReport = str($document->title)->contains(['Evaluierungsbericht', 'evaluierungsbericht', 'evaluation']);

        if($isReport){
            return DocumentType::EVALUATION_REPORT;
        }

        return DocumentType::DOCUMENT;
    }


    
}
