<?php

namespace App\Catalog\Flow;

enum FlowTargetEntity: int
{
    /**
     * The flow operates on documents
     */
    case DOCUMENT = 1;
}
