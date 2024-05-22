<?php

namespace App\Actions\Summary;

use App\Models\Document;
use App\Models\DocumentSummary;
use App\Models\User;
use PrinsFrank\Standards\Language\LanguageAlpha2;

class UpdateSummary
{
    /**
     * Update an existing document summary
     *
     * @param  \App\Models\DocumentSummary  $summary
     */
    public function __invoke(DocumentSummary $summary, string $text, ?LanguageAlpha2 $language = null, User $user = null): DocumentSummary
    {
        if(trim($summary->text) === trim($text)){
            // No changes, so we don't update
            return $summary;
        }

        if($summary->isAiGenerated() || $summary->user_id !== $user?->getKey()){
            return (new SaveSummary())($summary->document, $text, $language, $user);
        }

        $summary->text = $text;
        $summary->language = $language;

        $summary->save();

        return $summary->fresh();
    }

}
