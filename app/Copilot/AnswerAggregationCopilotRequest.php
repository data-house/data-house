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
            'q_id' => $this->id,
            'arguments' => ['text' => $this->question],
            'template_id' => '0', // template id corresponding to free multiple question on the backend
            'lang' => $this->language,
            'answers' => $this->documents,
        ];
    }

}
