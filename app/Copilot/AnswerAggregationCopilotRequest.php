<?php

namespace App\Copilot;

use Illuminate\Support\Arr;
use JsonSerializable;

class AnswerAggregationCopilotRequest extends CopilotRequest
{
    /**
     * Get the JSON serializable representation of the object.
     *
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'question' => [
                'id' => $this->id,
                'text' => $this->question,
                'lang' => $this->language,
            ],
            'transformation' => [
                'id' => $this->guidanceTemplate ?? '0', // template id corresponding to free multiple question on the backend
                'args' => [$this->question],
            ],
            'answers' => collect($this->documents)->filter()
                ->values()
                ->map(function($answer){
                    return [
                        ...Arr::only($answer, ['text', 'id', 'lang']),
                        'refs' => $answer['references'] ?? $answer['refs'],
                    ];
                })
                ->toArray(),
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
        return hash('sha512', $this->question . '-'. ($this->guidanceTemplate ?? '0') .'-');
    }

}
