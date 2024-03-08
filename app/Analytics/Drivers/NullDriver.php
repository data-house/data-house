<?php

namespace App\Analytics\Drivers;

use App\Analytics\Contracts\Driver;
use Illuminate\Contracts\Support\Htmlable;

class NullDriver implements Driver
{
    public function trackerCode(): Htmlable
    {
        return str('')->toHtmlString();
    }
}