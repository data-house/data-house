<?php

namespace App\Copilot;

use Illuminate\Support\Arr;
use JsonSerializable;
use OneOffTech\LibrarianClient\Dto\Question;
use OneOffTech\LibrarianClient\Dto\QuestionTransformation;
use OneOffTech\LibrarianClient\TransformType;

class CopilotRequest implements JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $question,
        public readonly string|array $documents,
        public readonly ?string $language = null,
        public readonly ?string $guidanceTemplate = null,
        public readonly ?array $guidanceTemplateAppend = null,
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
                'question' => [
                    'id' => $this->id,
                    'text' => $this->question,
                    'lang' => $this->language,
                ],
                'transformation' => [
                    'id' => $this->guidanceTemplate ?? '0', // template id corresponding to free multiple question on the backend
                    'args' => [$this->question],
                ]
            ];
        }

        return [
            'id' => $this->id,
            'text' => $this->question,
            'lang' => $this->language,
        ];
    }

    public function getLibrarianQuestion(): Question
    {
        return new Question(
            id: $this->id,
            language: $this->language,
            text: $this->question,
        );
    }
    
    public function getLibrarianQuestionTransformation(): QuestionTransformation
    {
        return new QuestionTransformation(
            id: TransformType::from($this->guidanceTemplate ?? '0'),
            args: [$this->question],
            append: $this->guidanceTemplateAppend ?? [],
        );
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
        return hash('sha512', $this->question . '-' . implode('-', $this->documents));
    }

}
