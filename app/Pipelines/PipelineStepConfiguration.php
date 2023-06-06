<?php

namespace App\Pipelines;

use JsonSerializable;

class PipelineStepConfiguration implements JsonSerializable
{
    /**
     * The job model for this step.
     *
     * @var string
     */
    public $job;

    /**
     * The name of the step.
     *
     * @var string
     */
    public $name;

    /**
     * The step description.
     *
     * @var string
     */
    public $description;

    /**
     * Create a new pipe instance.
     *
     * @param  string  $job
     * @return void
     */
    public function __construct(string $job)
    {
        $this->job = $job;
    }

    /**
     * Name of the step.
     *
     * @param  string  $name
     * @return $this
     */
    public function name(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Describe the step.
     *
     * @param  string  $description
     * @return $this
     */
    public function description(string $description)
    {
        $this->description = $description;

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
            'job' => $this->job,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
