<?php

namespace App\Actions\Summary;

use App\Models\Document;
use App\Models\DocumentSummary;
use App\Models\User;
use PrinsFrank\Standards\Language\LanguageAlpha2;

class SaveSummary
{
    /**
     * Save a new document summary
     *
     * @param  \App\Models\Document  $document
     */
    public function __invoke(Document $document, string $text, ?LanguageAlpha2 $language = null, User $user = null, bool $wholeDocument = true): DocumentSummary
    {
        return $document->summaries()->create([
            'text' => $text,
            'ai_generated' => is_null($user),
            'language' => $language,
            'user_id' => $user?->getKey(),
            'all_document' => $wholeDocument,
        ]);
    }

}
