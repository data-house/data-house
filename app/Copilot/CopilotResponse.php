<?php

namespace App\Copilot;

use JsonSerializable;

class CopilotResponse implements JsonSerializable
{
    public function __construct(
        public readonly string $text,

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
            'text' => $this->text,
        ];
    }

}
