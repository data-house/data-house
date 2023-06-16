<?php

namespace App\DocumentConversion;

use App\DocumentConversion\Drivers\OnlyOfficeDriver;
use Illuminate\Support\Arr;
use Illuminate\Support\Manager;
use Illuminate\Support\Str;
use InvalidArgumentException;

class DocumentConversionManager extends Manager
{

    /**
     * Create an instance of the specified driver.
     *
     * @return \App\DocumentConversion\Contracts\Driver
     */
    protected function createOnlyOfficeDriver()
    {
        return new OnlyOfficeDriver(config('conversion'));
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
        return DocumentConversionDriver::ONLY_OFFICE->value;
    }
}