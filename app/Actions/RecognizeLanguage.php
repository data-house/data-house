<?php

namespace App\Actions;

use App\Models\Document;
use Illuminate\Support\Collection;
use PrinsFrank\Standards\Language\LanguageAlpha3Terminology;
use Oneofftech\LaravelLanguageRecognizer\Support\Facades\LanguageRecognizer;

class RecognizeLanguage
{
    protected const ALLOWED_LANGUAGES = [
        // TODO: Needs to be revised, this is a preliminary implementation as we only target those languages.
        LanguageAlpha3Terminology::English,
        LanguageAlpha3Terminology::Italian,
        LanguageAlpha3Terminology::Spanish_Castilian,
        LanguageAlpha3Terminology::French,
        LanguageAlpha3Terminology::German,
        LanguageAlpha3Terminology::Russian,
        LanguageAlpha3Terminology::Ukrainian,
    ];

    protected const MINIMUM_CONFIDENCE = 0.8;

    /**
     * Attempt to recognize the most probable language of the documen's content
     *
     * @param  \App\Models\Document  $document
     */
    public function __invoke(Document $document): ?Collection
    {
        $content = $document->getContent();

        if($content->isEmpty()){
            return collect();
        }

        $possibleLanguages = collect(LanguageRecognizer::recognize($content->all()));

        // We check if the current recognized languages are within the list of acceptable ones
        // and we keep only the ones above a certain threshold
        // As a workaround on the current meaning of the languages field we transform
        // the result in a collection of alpha-2 iso codes

        return $possibleLanguages
            ->only(collect(self::ALLOWED_LANGUAGES)->map->value)
            ->map(function($confidence, $code){

                if($confidence < self::MINIMUM_CONFIDENCE){
                    return null;
                }
                
                $lang = LanguageAlpha3Terminology::tryFrom($code) ?? LanguageAlpha3Terminology::tryFrom($code);

                return [
                    'alpha2' => $lang->toLanguageAlpha2(),
                    'alpha3' => $code,
                    'confidence' => $confidence,
                ];
            })
            ->values()
            ->filter()
            ->sortByDesc('confidence')
            ->pluck('alpha2');
    }

}
