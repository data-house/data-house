<?php

namespace App\Copilot;

use App\Copilot\Engines\NullEngine;
use App\Copilot\Engines\OaksEngine;
use Illuminate\Support\Manager;

class CopilotManager extends Manager
{

    /**
     * Create an instance of the specified driver.
     *
     * @return \App\Copilot\Engines\OaksEngine
     */
    protected function createOaksDriver()
    {
        return new OaksEngine($this->getConfig('oaks'));
    }

    /**
     * Create a null engine instance.
     *
     * @return \App\Copilot\Engines\NullEngine
     */
    public function createNullDriver()
    {
        return new NullEngine;
    }
    

    /**
     * Get the copilot engine configuration.
     *
     * @param  string  $name
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->config["copilot.engines.{$name}"] ?: [];
    }

    /**
     * Forget all of the resolved driver instances.
     *
     * @return $this
     */
    public function forgetDrivers()
    {
        $this->drivers = [];

        return $this;
    }

    /**
     * Set the container instance used by the manager.
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return $this
     */
    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the default Copilot driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        if (is_null($driver = config('copilot.driver'))) {
            return 'null';
        }

        return $driver;
    }
}