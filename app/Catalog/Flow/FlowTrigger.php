<?php

namespace App\Catalog\Flow;

enum FlowTrigger: int
{
    /**
     * Manually execute the flow
     */
    case MANUAL = 1;
}
