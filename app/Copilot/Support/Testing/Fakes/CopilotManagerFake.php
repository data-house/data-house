<?php

namespace App\Copilot\Support\Testing\Fakes;

use App\Copilot\CopilotManager;
use App\Copilot\Engines\Engine;
use App\Copilot\Support\Testing\Fakes\FakeEngine;
use BackedEnum;
use BadMethodCallException;
use Closure;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Queue\CallQueuedClosure;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Testing\Fakes\Fake;
use Illuminate\Support\Traits\ReflectsClosures;
use InvalidArgumentException;
use PHPUnit\Framework\Assert as PHPUnit;

class CopilotManagerFake extends CopilotManager implements Fake
{
    use ReflectsClosures;

    /**
     * Create a new fake queue instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        parent::__construct($app);
    }


    public function driver($driver = null)
    {
        $driver = $driver ?: $this->getDefaultDriver();

        $driver = $driver instanceof BackedEnum && is_string($driver->value) ? $driver->value : $driver;

        if (is_null($driver)) {
            throw new InvalidArgumentException(sprintf(
                'Unable to resolve NULL driver for [%s].', static::class
            ));
        }

        // If the given driver has not been created before, we will create the instances
        // here and cache it so we can return it next time very quickly. If there is
        // already a driver created by this name, we'll just return that instance.
        if (! isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->createFakeDriver($driver);
        }

        return $this->drivers[$driver];
    }


    /**
     * Create an instance of the Fake driver for testing purposes.
     *
     * @return \App\PdfProcessing\Contracts\Driver
     */
    protected function createFakeDriver($driver): Engine
    {
        return new FakeEngine($this->getConfig($driver));
    }
}
