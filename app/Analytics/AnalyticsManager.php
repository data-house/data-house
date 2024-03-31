<?php

namespace App\Analytics;

use App\Analytics\Drivers\MatomoDriver;
use App\Analytics\Drivers\NullDriver;
use Illuminate\Support\Manager;
use InvalidArgumentException;

class AnalyticsManager extends Manager
{

    /**
     * Create an instance of the specified driver.
     *
     * @return \App\Analytics\Contracts\Driver
     */
    protected function createMatomoDriver()
    {
        $config = $this->getConfig('matomo');

        return new MatomoDriver($config);
    }
    
    /**
     * Create an instance of the specified driver.
     *
     * @return \App\Analytics\Contracts\Driver
     */
    protected function createNullDriver()
    {
        return new NullDriver();
    }
    

    /**
     * Get the PDF processor configuration.
     *
     * @param  string  $name
     * @return array
     */
    protected function getConfig($name)
    {
        $driverSpecific = $this->config["analytics.processors.{$name}"] ?: [];

        $globalTrackingSettings = ['tracking' => $this->config["analytics.tracking"] ?? []];

        return array_merge($globalTrackingSettings, $driverSpecific);
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
        return $this->config['analytics.default'] ?? 'null';
    }
}