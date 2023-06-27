<?php

namespace App\PdfProcessing;

use JsonSerializable;

class DocumentContent implements JsonSerializable
{
    public function __construct(
        public readonly string|array $raw,

        )
    {
    }

    /**
     * Return the whole document as plain text
     */
    public function all(): string
    {
        if(is_array($this->raw)){
            return collect($this->raw)->join('\f');
        }
        return $this->raw;
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
            'raw' => $this->raw,
        ];
    }

}
