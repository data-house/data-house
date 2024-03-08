<?php

namespace Tests\Feature\Analytics\Drivers;

use App\Analytics\Drivers\NullDriver;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NullDriverTest extends TestCase
{
    
    public function test_null_driver_has_no_tracker_code()
    {
        $driver = new NullDriver();

        $tracking = $driver->trackerCode();

        $this->assertInstanceOf(Htmlable::class, $tracking);
        $this->assertEquals('', $tracking->toHtml());
    }

}
