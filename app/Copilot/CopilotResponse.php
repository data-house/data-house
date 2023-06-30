<?php

namespace App\Copilot;

use JsonSerializable;
use \Illuminate\Support\Str;
use Illuminate\Contracts\Support\Htmlable;

class CopilotResponse implements JsonSerializable, Htmlable
{

    public readonly float $executionTime;

    public function __construct(
        public readonly string $text,
        public readonly array $references = []
        )
    {
    }

    public function setExecutionTime(float $duration): self
    {
        $this->executionTime = $duration;

        return $this;
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
            'text' => $this->text,
            'references' => $this->references,
        ];
    }

    /**
     * Get the html representation of this response
     */
    public function toHtml()
    {
        return Str::markdown($this->text);
    }

}
