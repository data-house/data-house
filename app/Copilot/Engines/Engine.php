<?php

namespace App\Copilot\Engines;

use App\Copilot\CopilotRequest;
use App\Copilot\CopilotResponse;

abstract class Engine
{
    protected readonly array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }


    /**
     * Update the given model in the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    abstract public function update($models);

    /**
     * Remove the given model from the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    abstract public function delete($models);

    /**
     * Ask the question to the engine.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @return mixed
     */
    abstract public function question(CopilotRequest $question): CopilotResponse;


}
