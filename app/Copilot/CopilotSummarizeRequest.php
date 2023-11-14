<?php

namespace App\Copilot;

use JsonSerializable;
use PrinsFrank\Standards\Language\LanguageAlpha2;

class CopilotSummarizeRequest implements JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $text,
        public readonly ?LanguageAlpha2 $language = null,

        )
    {
    }


    /**
     * Get the JSON serializable representation of the object.
     *
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
            'lang' => $this->language?->value,
        ];
    }

    /**
     * Calculate a hash representing the characteristics of the request.
     * 
     * This can be used to cache the request and the response.
     * 
     * @return string
     */
    public function hash(): string
    {
        return hash('sha512', $this->text);
    }

}
