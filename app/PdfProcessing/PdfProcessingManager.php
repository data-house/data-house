<?php

namespace App\PdfProcessing;

use App\PdfProcessing\Drivers\CopilotPdfParserDriver;
use App\PdfProcessing\Drivers\SmalotPdfParserDriver;
use App\PdfProcessing\Drivers\XpdfDriver;
use Illuminate\Support\Arr;
use Illuminate\Support\Manager;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PdfProcessingManager extends Manager
{

    /**
     * Create an instance of the specified driver.
     *
     * @return \App\PdfProcessing\Contracts\Driver
     */
    protected function createXpdfDriver()
    {
        return new XpdfDriver();
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \App\PdfProcessing\Contracts\Driver
     */
    protected function createSmalotDriver()
    {
        return new SmalotPdfParserDriver();
    }
    
    /**
     * Create an instance of the specified driver.
     *
     * @return \App\PdfProcessing\Contracts\Driver
     */
    protected function createCopilotDriver()
    {
        $config = $this->getConfig('copilot');

        return new CopilotPdfParserDriver($config);
    }

    /**
     * Get the PDF processor configuration.
     *
     * @param  string  $name
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->config["pdf.processors.{$name}"] ?: [];
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
        return $this->config['pdf.default'] ?? PdfDriver::SMALOT_PDF->value;
    }
}