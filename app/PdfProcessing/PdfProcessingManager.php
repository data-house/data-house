<?php

namespace App\PdfProcessing;

use App\PdfProcessing\Contracts\Driver;
use InvalidArgumentException;
use Illuminate\Support\Manager;
use App\PdfProcessing\Drivers\SmalotPdfParserDriver;
use App\PdfProcessing\Drivers\ParsePdfParserDriver;
use BackedEnum;

class PdfProcessingManager extends Manager
{


    /**
     * Get a driver instance.
     *
     * @param  \BackedEnum|string|null  $driver
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function driver($driver = null)
    {
        if($driver instanceof BackedEnum && is_string($driver->value)){
            return parent::driver($driver->value);
        }
        

        return parent::driver($driver);
    }


    /**
     * Create an instance of the Smalot driver.
     *
     * @return \App\PdfProcessing\Contracts\Driver
     */
    protected function createSmalotDriver(): Driver
    {
        return new SmalotPdfParserDriver();
    }
    
    /**
     * Create an instance of the Parse driver.
     *
     * @return \App\PdfProcessing\Contracts\Driver
     */
    protected function createParseDriver(): Driver
    {
        $config = $this->getConfig('parse');

        return new ParsePdfParserDriver($config);
    }

    /**
     * Get the PDF processor configuration.
     *
     * @param  string  $name
     * @return array
     */
    protected function getConfig($name): array
    {
        return $this->config["pdf.processors.{$name}"] ?: [];
    }

    /**
     * Forget all of the resolved driver instances.
     *
     * @return $this
     */
    public function forgetDrivers(): self
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
    public function setContainer($container): self
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return $this->config['pdf.default'] ?? PdfDriver::SMALOT->value;
    }
}