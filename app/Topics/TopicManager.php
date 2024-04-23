<?php

namespace App\Topics;

use App\Topics\Drivers\NullDriver;
use App\Topics\Drivers\JsonConceptsDriver;
use App\Topics\Drivers\JsonDriver;
use Illuminate\Support\Manager;
use InvalidArgumentException;

class TopicManager extends Manager
{

    /**
     * Create an instance of the specified driver.
     *
     * @return \App\Topics\Contracts\Driver
     */
    protected function createJsonDriver()
    {
        return new JsonDriver([
            'schemes' => config('topics.schemes'),
            ...config('topics.drivers.json')
        ]);
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \App\Topics\Contracts\Driver
     */
    protected function createNullDriver()
    {
        return new NullDriver([
            'schemes' => config('topics.schemes'),
            ...config('topics.drivers.json')
        ]);
    }
    
    /**
     * Create an instance of the specified driver.
     *
     * @return \App\Topics\Contracts\Driver
     */
    protected function createJsonConceptsDriver()
    {
        return new JsonConceptsDriver([
            'schemes' => config('topics.schemes'),
            ...config('topics.drivers.json-concepts')
        ]);
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
     * Get the default driver name.
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function getDefaultDriver()
    {
        return config('topics.default', 'json');
    }
}