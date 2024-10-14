<?php

namespace App\PdfProcessing\Support\Testing\Fakes;

use App\PdfProcessing\Contracts\Driver;
use App\PdfProcessing\PdfProcessingManager;
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

class PdfManagerFake extends PdfProcessingManager implements Fake
{
    use ReflectsClosures;

    /**
     * The original queue manager.
     *
     * @var \App\PdfProcessing\PdfProcessingManager
     */
    public $pdfManager;

    /**
     * The job types that should be intercepted instead of pushed to the queue.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $extractionsToFake;

    /**
     * Indicates if items should be serialized and restored when pushed to the queue.
     *
     * @var bool
     */
    protected bool $serializeAndRestore = false;

    /**
     * Create a new fake queue instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  array  $extractionsToFake
     * @param  \App\PdfProcessing\PdfProcessingManager|null  $queue
     * @return void
     */
    public function __construct($app, $extractionsToFake = [], $pdfManager = null)
    {
        parent::__construct($app);

        $this->extractionsToFake = Collection::wrap($extractionsToFake);
        $this->pdfManager = $pdfManager;
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
            $this->drivers[$driver] = $this->createFakeDriver();
        }

        return $this->drivers[$driver];
    }


    /**
     * Create an instance of the Fake driver for testing purposes.
     *
     * @return \App\PdfProcessing\Contracts\Driver
     */
    protected function createFakeDriver(): Driver
    {
        return new FakeParserDriver($this->extractionsToFake);
    }
}
