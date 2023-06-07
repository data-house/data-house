<?php

namespace App\Pipelines;

use JsonSerializable;

class PipelineConfiguration implements JsonSerializable
{
    /**
     * The key identifier for the pipe.
     *
     * @var string
     */
    public $key;

    /**
     * The name of the pipe.
     *
     * @var string
     */
    public $name;

    /**
     * The trigger condition of the pipe.
     *
     * @var \App\Pipelines\PipelineTrigger
     */
    public $trigger;

    /**
     * The pipe's steps.
     *
     * @var array
     */
    public $steps;

    /**
     * The pipe's description.
     *
     * @var string
     */
    public $description;

    /**
     * Create a new pipe instance.
     *
     * @param  string  $key
     * @param  string  $name
     * @param  array  $steps
     * @return void
     */
    public function __construct(string $key, PipelineTrigger $trigger, array $steps)
    {
        $this->key = $key;
        $this->trigger = $trigger;
        $this->steps = collect($steps)
            ->mapInto(PipelineStepConfiguration::class)
            ->toArray();
    }

    /**
     * Name the pipe.
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
     * Describe the pipe.
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
            'key' => $this->key,
            'trigger' => $this->trigger->value,
            'name' => $this->name,
            'description' => $this->description,
            'steps' => $this->steps,
        ];
    }
}
