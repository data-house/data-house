<?php

namespace App\Actions;

use App\Models\Document;
use Illuminate\Support\Collection;

class SuggestDocumentTags
{
    /**
     * Suggest document tags according to entries defined in an existing list
     *
     * @param  \App\Models\Document  $document
     */
    public function __invoke(string $list, Document $document): Collection
    {
        $response = $document->questionableUsing()->tag($list, $document);

        return collect($response);
    }

}
