<?php

namespace App\Topics\Contracts;

use Illuminate\Support\Collection;

interface Driver
{
    /**
     * All registered concepts
     */
    public function concepts(): Collection;
    
    /**
     * All registered schemes
     */
    public function schemes(): Collection;
    
    /**
     * Get the topics given their identifiers
     */
    public function from(array|Collection $names): Collection;
    
    /**
     * Get the topics as search filters
     */
    public function facets(): Collection;
}