<?php

namespace App\Copilot;

use Illuminate\Support\Arr;
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

    public function multipleQuestionRequest(): bool
    {
        return is_array($this->documents) && count($this->documents) > 1;
    }

    /**
     * Get the JSON serializable representation of the object.
     *
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        if($this->multipleQuestionRequest()){
            return [
                'q_id' => $this->id,
                'arguments' => ['text' => $this->question],
                'template_id' => '0', // template id corresponding to free multiple question on the backend
                'doc_list' => Arr::wrap($this->documents),
                'lang' => $this->language,
            ];
        }

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
