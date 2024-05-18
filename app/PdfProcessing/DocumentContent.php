<?php

namespace App\PdfProcessing;

use Illuminate\Support\Collection;
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

    public function collect(): Collection
    {
        return collect($this->raw);
    }

    /**
     * The document does not contain text. 
     * However It can be composed by images
     */
    public function isEmpty(): bool
    {
        if(is_array($this->raw)){
            return collect($this->raw)->filter()->isEmpty();
        }
        
        return str($this->raw)->trim()->isEmpty();
    }
    
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
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
