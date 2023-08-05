<?php

namespace App\Copilot;

use JsonSerializable;

class CopilotSummarizeRequest implements JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $text,
        public readonly ?string $language = null,

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
            'doc_id' => $this->id,
            'text' => $this->text,
            'lang' => $this->language,
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