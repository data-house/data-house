<?php

namespace App\DocumentThumbnail;

use App\DocumentThumbnail\Drivers\ImaginaryDriver;
use App\DocumentThumbnail\Drivers\OnlyOfficeDriver;
use Illuminate\Support\Arr;
use Illuminate\Support\Manager;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Tests\Feature\DocumentThumbnail\Drivers\ImaginaryDriverTest;

class DocumentThumbnailManager extends Manager
{

    /**
     * Create an instance of the specified driver.
     *
     * @return \App\DocumentThumbnail\Contracts\Driver
     */
    protected function createImaginaryDriver()
    {
        return new ImaginaryDriver([
            'disk' => config('thumbnail.disk'),
            ...config('thumbnail.drivers.imaginary')
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
        return DocumentThumbnailDriver::IMAGINARY->value;
    }
}