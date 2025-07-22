<?php

namespace App\Catalog\Flow;

enum FlowSourceEntity: int
{
    /**
     * The flow operates on documents
     */
    case DOCUMENT = 1;
}
