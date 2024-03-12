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

        return $response->map(function($tag){
            return [
                'tag_id' => $tag['topic_id'],
                'tag_name' => $tag['topic_name'],
                'score' => $tag['distance'],
                'highlight' => collect($tag['based_on'] ?? [])->map(function($match){
                    return [
                        'match' => $match['text'],
                        'score' => $match['score'],
                        'page' => $match['metadata']['page_number'],
                    ];
                }),
            ];
        });
    }

}
