<?php

namespace App\PdfProcessing;

use Illuminate\Support\Arr;
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

    public function pages(): array
    {
        if(!is_array($this->raw)){
            return [1 => $this->raw];
        }

        return $this->raw;
    }

    /**
     * Get the JSON serializable representation of the object.
     *
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize(): mixed
    {
        return [
            'raw' => $this->raw,
        ];
    }

    public function asStructured(): array
    {
        return [
            "type" => "doc",
            "content" => [[
                "category" => "page",
                "attributes" => [
                    "page" => 1
                ],
                "content" => [
                    [
                        "role" => "body",
                        "text" => $this->raw,
                        "marks" => [],
                        "attributes" => [],
                    ]
                ]
            ]],
        ];
    }

}
