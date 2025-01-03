<?php

namespace App\Actions;

use App\Models\Document;
use App\Models\ImportDocument;
use Illuminate\Support\Facades\DB;

class DeleteDocument
{
    /**
     * Delete a document
     *
     * @param  \App\Models\Document  $document
     */
    public function __invoke(Document $document): void
    {
        DB::transaction(function() use ($document): void{
            $document->importDocument?->wipe();

            ImportDocument::whereDocumentHash($document->document_hash)->whereNull('document_id')->get()->each->wipe();

            $document->pipelineRuns->each->delete();
            
            $document->summaries->each->delete();
            
            $document->questions->each->delete();

            rescue(fn() => $document->unsearchable());

            rescue(fn() => $document->unquestionable());

            $document->wipe();
        });
    }

}
