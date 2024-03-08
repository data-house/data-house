<?php

namespace App\Analytics\Contracts;

use Illuminate\Contracts\Support\Htmlable;

interface Driver
{
    /**
     * Get the tracking script
     * 
     * @return 
     */
    public function trackerCode(): Htmlable;

    
}