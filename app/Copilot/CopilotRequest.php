<?php

namespace App\Copilot;

use JsonSerializable;

class CopilotRequest implements JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $question,
        public readonly string|array $documents,
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
            'q_id' => $this->id,
            'q' => $this->question,
            'doc_id' => $this->documents,
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
        return hash('sha512', $this->question . '-' . join('-', $this->documents));
    }

}
